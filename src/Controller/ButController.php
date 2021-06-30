<?php

namespace App\Controller;

use App\Entity\Annee;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\Semestre;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/but', name: 'but_')]
class ButController extends AbstractController
{
    #[Route('/{annee}', name: 'annee', requirements: ['annee' => '\d+'])]
    public function index(Annee $annee): Response
    {
        //todo: filtrer par année...

        return $this->render('but/index.html.twig', [
            'annee' => $annee
        ]);
    }

    #[Route('/ressources/{annee}/{semestre}', name: 'ressources_annee', requirements: ['annee' => '\d+'])]
    public function ressources(ApcRessourceRepository $apcRessourceRepository, Annee $annee, Semestre $semestre = null): Response
    {
        $ressources = $apcRessourceRepository->findByAnneeArray($annee);

        return $this->render('but/ressources.html.twig', [
            'ressources' => $ressources,
            'annee' => $annee,
            'selectSemestre' => $semestre
        ]);
    }

    #[Route('/sae/{annee}/{semestre}', name: 'sae_annee', requirements: ['annee' => '\d+'])]
    public function saes(ApcSaeRepository $apcSaeRepository, Annee $annee, Semestre $semestre = null): Response
    {
        $saes = $apcSaeRepository->findByAnneeArray($annee);

        return $this->render('but/saes.html.twig', [
            'annee' => $annee,
            'saes' => $saes,
            'selectSemestre' => $semestre
        ]);
    }

    #[Route("/fiche-ressource/{apcRessource}", name:"fiche_ressource")]
    public function ficheRessource(
        ApcRessource $apcRessource
    ): Response {
//Todo: vérifier que la ressource ou l'nnée est bien dans la spécialité du connecté (si changement d'id dans l'URL)
        return $this->render('but/ficheRessource.html.twig', [
            'apc_ressource' => $apcRessource,
        ]);
    }

    #[Route('/fiche-sae/{apcSae}', name: 'fiche_sae')]
    public function ficheSae(
        ApcSae $apcSae
    ): Response {
//Todo: vérifier que la ressource ou l'nnée est bien dans la spécialité du connecté (si changement d'id dans l'URL)
        return $this->render('but/ficheSae.html.twig', [
            'apc_sae' => $apcSae,
        ]);
    }
}
