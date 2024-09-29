<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route("/login", name:"app_login")]
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('homepage');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($request->query->has('email')) {
            $lastUsername = $request->query->get('email');
        }

        if ($request->query->has('message')) {
            if ($request->query->get('message') === 'mail-verified') {
                $success = 'Votre adresse email a été vérifiée. Vous pouvez vous connecter.';
            }
        }

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'success' => $success ?? null]);
    }

    #[Route("/logout", name:"app_logout")]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
