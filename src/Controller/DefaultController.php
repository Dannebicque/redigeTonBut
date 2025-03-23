<?php

namespace App\Controller;

use App\Entity\Departement;
use App\Repository\DepartementRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
        ]);
    }

    #[Route('/direct/{departement}', name: 'homepage_direct_specialite')]
    public function directSpecialite(
        DepartementRepository $departementRepository,
        RequestStack $requestStack, ?string $departement = null): Response
    {
        if( $departement !== null) {
            $dept = $departementRepository->findOneBy(['sigle' => $departement]);
            if ($dept) {
                $requestStack->getSession()->set('departement', $dept->getId());
            }
        }

        return $this->redirectToRoute('homepage_specialite');
    }

    #[Route('/specialite', name: 'homepage_specialite')]
    public function indexSpecialite(): Response
    {
        return $this->render('default/index-specialite.html.twig', [
        ]);
    }

    #[Route('/change-specialite/{departement}', name: 'change_specialite')]
    public function changeSpecialite(RequestStack $requestStack, Departement $departement): Response
    {
        if ($this->isGranted('ROLE_GT') || $this->isGranted('ROLE_CPN') || $this->isGranted('ROLE_IUT') || $this->isGranted('ROLE_CPN_LECTEUR')) {

            $requestStack->getSession()->set('departement', $departement->getId());

            if ($this->isGranted('ROLE_IUT')) {
                return $this->redirectToRoute('homepage_specialite');
            }

            return $this->redirectToRoute('homepage');
        }

        throw new Exception('Fonctionnalité interdite au regard de vos droits.');
    }
}
