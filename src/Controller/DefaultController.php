<?php

namespace App\Controller;

use App\Entity\Departement;
use Exception;
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

    #[Route('/specialite', name: 'homepage_specialite')]
    public function indexSpecialite(): Response
    {
        return $this->render('default/index-specialite.html.twig', [
        ]);
    }

    #[Route('/change-specialite/{departement}', name: 'change_specialite')]
    public function changeSpecialite(SessionInterface $session, Departement $departement): Response
    {
        if ($this->isGranted('ROLE_GT') || $this->isGranted('ROLE_CPN') || $this->isGranted('ROLE_IUT') || $this->isGranted('ROLE_CPN_LECTEUR')) {

            $session->set('departement', $departement->getId());

            if ($this->isGranted('ROLE_IUT')) {
                return $this->redirectToRoute('homepage_specialite');
            }

            return $this->redirectToRoute('homepage');
        }

        throw new Exception('Fonctionnalité interdite au regard de vos droits.');
    }
}
