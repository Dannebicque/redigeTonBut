<?php

namespace App\Controller;

use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DurableController extends AbstractController
{
    #[Route('/durable', name: 'app_durable')]
    public function index(
        ApcRessourceRepository $apcRessourceRepository,
        ApcSaeRepository $apcSaeRepository,
        Request $request
    ): Response {
        $ressources = null;
        $saes = null;

        $keyWords = [];
        foreach ($request->request as $key => $value) {
            if (str_starts_with($key, 'name_')) {
                if (trim($value) !== '') {
                    $keyWords[] = trim($value);
                }
            }
        }
        if ($request->isMethod('POST')) {
            if (in_array('ressource', (array)$request->request->get('type'), true)) {
                $ressources = $apcRessourceRepository->findByKeywords($keyWords);
            }

            if (in_array('sae', (array)$request->request->get('type'), true)) {
                $saes = $apcSaeRepository->findByKeywords($keyWords);
            }
        }

        return $this->render('durable/index.html.twig', [
            'ressources' => $ressources,
            'saes' => $saes,
            'keyWords' => $keyWords,
        ]);
    }
}
