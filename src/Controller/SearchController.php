<?php

namespace App\Controller;

use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends BaseController
{
    #[Route('/search', name: 'search', options: ['expose' => 'true'])]
    public function index(
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository
    ): Response
    {
        $t = [];
        $saes = $apcSaeRepository->findByDepartement($this->getDepartement());
        $ressources = $apcRessourceRepository->findByDepartement($this->getDepartement());

        foreach ($saes as $sae) {
            $t[] = [
                'label' => $sae->getDisplay(),
                'url' => $this->generateUrl('but_fiche_sae', ['apcSae' => $sae->getId()])
            ];
        }

        foreach ($ressources as $ressource) {
            $t[] = [
                'label' => $ressource->getDisplay(),
                'url' => $this->generateUrl('but_fiche_ressource', ['apcRessource' => $ressource->getId()])
            ];
        }


        return $this->json($t);
    }
}
