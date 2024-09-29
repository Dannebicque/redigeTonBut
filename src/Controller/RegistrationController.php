<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
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
    #[Route('/demande', name: 'app_register')]
    public function register(
        EntityManagerInterface       $entityManager,
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

            $user->setActif(true);
            $entityManager->persist($user);
            $entityManager->flush();


            $email = (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject('[ORéBUT] Merci de confirmer votre email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
                ->context([
                    'user' => $user,
                    'token' => md5($user->getEmail()),
                    'expiredAt' => (new \DateTime())->modify('+1 hour')->getTimestamp()
                    ]);


            // generate a signed url and email it to the user
            $mailer->send($email);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/confirmation-email/{email}', name: 'app_confirmation_email')]
    public function confirmationEmail(
        MailerInterface $mailer,
        UserRepository $userRepository,
        string         $email
    ): Response
    {
        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            $this->addFlash('danger', 'Aucun utilisateur trouvé avec cet email.');
            return $this->redirectToRoute('app_register');
        }

        $templEmail = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('[ORéBUT] Merci de confirmer votre email')
            ->htmlTemplate('registration/confirmation_email.html.twig')
            ->context([
                'user' => $user,
                'token' => md5($user->getEmail()),
                'expiredAt' => (new \DateTime())->modify('+1 hour')->getTimestamp()
            ]);


        // generate a signed url and email it to the user
        $mailer->send($templEmail);

        $this->addFlash('success', 'Un email de confirmation a été envoyé à ' . $user->getEmail() . '.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/attente-validation', name: 'app_register_wait')]
    public function waitFormConfirmation(): Response
    {
        return $this->render('registration/waitFormConfirmation.html.twig', [
        ]);
    }

    #[Route('/verification-email', name: 'app_verify_email')]
    public function verifyUserEmail(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Request $request
    ): Response
    {
        $email = $request->query->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);
        $token = $request->query->get('token');
        $expiredAt = $request->query->get('expiredAt');

        if ($user === null) {
            $this->addFlash('danger', 'Aucun utilisateur trouvé avec cet email.');
            return $this->redirectToRoute('app_register');
        }

        if (md5($user->getEmail()) !== $token) {
            $this->addFlash('danger', 'Token invalide.');
            return $this->redirectToRoute('app_register');
        }

        //créer un objet DateTime avec la date d'expiration en texte
        $expiredAt = (new \DateTime())->setTimestamp($expiredAt);

        if ((new \DateTime()) > $expiredAt) {
            $this->addFlash('danger', 'Le lien a expiré.');
            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);
        $user->setActif(true);

        $entityManager->flush();


        $this->addFlash('success', 'Votre adresse email a été vérifée.');

        return $this->redirectToRoute('app_login', [
            'email' => $user->getEmail(),
            'message' => 'mail-verified'
        ]);
    }
}
