<?php

namespace App\Controller;

use App\Entity\Constantes;
use App\Form\UserType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profil', name: 'profil_')]
class ProfilController extends BaseController
{
    #[Route('/', name: 'profil')]
    public function profil(
        Request $request
    ): Response {
        $form = $this->createForm(UserType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('profil_profil');
        }

        return $this->render('profil/profil.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/theme', name: 'theme')]
    public function theme(): Response
    {
        return $this->render('profil/theme.html.twig', [
        ]);
    }

    #[Route('/password', name: 'password')]
    public function password(
        UserPasswordHasherInterface $userPasswordHasher,
        Request $request): Response
    {
        $formPassword = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('profil_password')
        ])
            ->add('oldPassword', PasswordType::class, ['label' => 'Ancien mot de passe'])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe'],
            ])
            ->getForm();
        $formPassword->handleRequest($request);
        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $obj = $formPassword->getData();
            //vérifier que c'est le bon...
            if($userPasswordHasher->isPasswordValid($this->getUser(), $obj['oldPassword']))
            {
                $this->getUser()->setPassword($userPasswordHasher->hashPassword($this->getUser(), $obj['newPassword']));
                $this->entityManager->flush();
                $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Modification du mot de passe effectuée');
            } else {
                $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Le mot de passe saisie ne correspond pas');
            }
        }
        return $this->render('profil/password.html.twig', [
            'form' => $formPassword->createView()
        ]);
    }
}
