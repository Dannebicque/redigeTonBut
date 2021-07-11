<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\EmailActivation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Message;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/administration/utilisateur', name: 'administration_utilisateur_')]
class UserController extends BaseController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        UserRepository $userRepository
    ): Response {
        if ($this->isGranted('ROLE_GT')) {
            $users = $userRepository->findAll();
        } else {
            $users = $userRepository->findByDepartement($this->getDepartement());
        }

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        UserPasswordHasherInterface $encoder,
        MailerInterface $mailer,
        Request $request
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['droit_gt' => $this->isGranted('ROLE_GT')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = random_bytes(10);
            $user->setPassword($encoder->hashPassword($password));
            $user->setActif(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            if ($user->getDepartement() !== null) {
                $email = (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->subject('[ORéBUT] Un compte a été cré sur l\'application')
                    ->htmlTemplate('registration/creation_compte_email.html.twig')
                    ->context(['user' => $user, 'password' => $password]);
                $mailer->send($email);
            }

            return $this->redirectToRoute('administration_utilisateur_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(
        User $user
    ): Response {
        if (!($this->isGranted('ROLE_GT') || $this->isGranted('ROLE_PACD') || $this->isGranted('ROLE_CPN'))) {
            throw new AccessDeniedException('Vous ne disposez pas des droits suffisants');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user
    ): Response {
        $form = $this->createForm(UserType::class, $user, ['droit_gt' => $this->isGranted('ROLE_GT')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('administration_utilisateur_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/activation/{role}', name: 'active', methods: ['GET'])]
    public function active(
        EmailActivation $emailActivation,
        User $user,
        string $role = 'ROLE_LECTEUR'
    ): Response {
        $user->setActif(true);
        if ($role === 'ROLE_LECTEUR' || $role === 'ROLE_EDITEUR') {
            $user->setRoles([$role]);
        }
        $emailActivation->sendEmailConfirmation($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('administration_utilisateur_index');
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('administration_utilisateur_index');
    }
}
