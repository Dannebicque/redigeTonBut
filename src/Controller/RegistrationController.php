<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/inscription')]
class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/', name: 'app_register')]
    public function register(
        MailerInterface             $mailer,
        Request                     $request,
        UserPasswordHasherInterface $passwordEncoder
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            //set email
            $email = $form->get('email')->getData();
            //vérifier si pas de @ sinon retirer tout ce qu'il y a, et ajouter le domaine
            if (str_contains($email, '@')) {
                //on retire tout ce qu'il y a après le @
                $email = substr($email, strpos($email, '@'), strlen($email));
            }

            $user->setEmail($email . '@' . $form->get('domaine')->getData()->getUrl());

            // $user->setIsVerified(true);
            $user->setActif(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->subject('[ORéBUT] Merci de confirmer votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context(['user' => $user])
            );
//            if ($user->getDepartement() !== null && $user->getDepartement()->getPacd() !== null) {
//                $email = (new TemplatedEmail())
//                    ->to($user->getDepartement()->getPacd()->getEmail())
//                    ->subject('[ORéBUT] Demande d\'accès à l\'application')
//                    ->htmlTemplate('registration/nouvelle_demande_email.html.twig')
//                    ->context(['user' => $user]);
//                $mailer->send($email);
//            }
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/confirmation-email/{email}', name: 'app_confirmation_email')]
    public function confirmationEmail(
        UserRepository $userRepository,
        string         $email
    ): Response
    {
        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            $this->addFlash('danger', 'Aucun utilisateur trouvé avec cet email.');
            return $this->redirectToRoute('app_register');
        }

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject('[ORéBUT] Merci de confirmer votre email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
                ->context(['user' => $user])
        );

        $this->addFlash('success', 'Un email de confirmation a été envoyé à ' . $user->getEmail() . '.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/attente-validation', name: 'app_register_wait')]
    public function waitFormConfirmation(): Response
    {
        return $this->render('registration/waitFormConfirmation.html.twig', [
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre adresse email a été vérifée.');

        return $this->redirectToRoute('homepage');
    }
}
