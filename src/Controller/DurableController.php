<?php

namespace App\Controller;

use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DurableController extends AbstractController
{
    #[Route('/durable', name: 'app_durable')]
    public function index(
        ApcRessourceRepository $apcRessourceRepository,
        ApcSaeRepository $apcSaeRepository,

    ): Response
    {

        $ressources = $apcRessourceRepository->findByDD();
        $saes = $apcSaeRepository->findByDD();

        return $this->render('durable/index.html.twig', [
            'ressources' => $ressources,
            'saes' => $saes
        ]);
    }
}
