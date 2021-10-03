<?php

namespace App\Controller;

use App\Entity\Departement;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
        ]);
    }

    #[Route('/change-specialite/{departement}', name: 'change_specialite')]
    public function changeSpecialite(SessionInterface $session, Departement $departement): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CPN');

        $session->set('departement', $departement->getId());
        return $this->redirectToRoute('homepage');
    }
}
