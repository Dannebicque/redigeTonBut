<?php

namespace App\Controller;

use App\Classes\Import\MyUpload;
use App\Classes\Import\UserImport;
use App\Entity\Constantes;
use App\Entity\User;
use App\Event\UserEvent;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\EmailActivation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    #[Route('/upload', name: 'import', methods: ['GET', 'POST'])]
    public function upload(
        MyUpload $myUpload,
        UserImport $userImport,
        Request $request
    ): Response {

        if ($request->isMethod('POST')) {
            //gestion de l'upload
            $fichier = $myUpload->upload($request->files->get('fichier'), 'temp/', ['xlsx']);
            if ($this->isGranted('ROLE_GT')) {
                $userImport->import($fichier);
                $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Liste importée avec succès');
            } elseif ($this->isGranted('ROLE_PACD')) {
                $userImport->importDepartement($fichier, $this->getDepartement());
                $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Liste importée avec succès');
            } else {
                $this->addFlashBag(Constantes::FLASHBAG_ERROR,
                    'Erreur lors de l\'import. Vous n\'avez pas les droits requis.');
            }

            unlink($fichier);

            return $this->redirectToRoute('administration_utilisateur_index');
        }

        return $this->render('user/upload.html.twig', [

        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        EventDispatcherInterface $eventDispatcher,
        UserPasswordHasherInterface $encoder,
        Request $request
    ): Response {
        $user = new User();
        $user->setActif(true);

        if (!$this->isGranted('ROLE_GT') && $this->getDepartement() !== null) {
            $user->setDepartement($this->getDepartement());
        }

        $form = $this->createForm(UserType::class, $user, ['droit_gt' => $this->isGranted('ROLE_GT')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = mb_substr(md5(mt_rand()), 0, 10);
            $user->setPassword($encoder->hashPassword($user, $password));
            $user->setIsVerified(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            if ($user->isActif() === true) {
                $userEvent = new UserEvent($user);
                $userEvent->setPassword($password);
                $eventDispatcher->dispatch($userEvent, UserEvent::CREATION_COMPTE);
            }

            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Utilisateur ajouté. Email envoyé.');

            return $this->redirectToRoute('administration_utilisateur_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/init-password/{id}', name: 'init_password', methods: ['GET'])]
    public function initPassword(
        EventDispatcherInterface $eventDispatcher,
        UserPasswordHasherInterface $encoder,
        User $user
    ): Response {


        $password = mb_substr(md5(mt_rand()), 0, 10);
        $user->setPassword($encoder->hashPassword($user, $password));
        $this->entityManager->flush();

        if ($user->isVerified()) {
            $userEvent = new UserEvent($user);
            $userEvent->setPassword($password);
            $eventDispatcher->dispatch($userEvent, UserEvent::INIT_PASSWORD);
        }

        $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Mot de passe réinitialisé et envoyé à l\'utilisateur.');

        return $this->redirectToRoute('administration_utilisateur_index');
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
