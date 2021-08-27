<?php

namespace App\Controller;

use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\Semestre;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
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

    #[Route('/ressources/{annee}/{semestre}', name: 'ressources_annee_semestre', requirements: ['annee' => '\d+'])]
    #[Route('/ressources/{annee}/{semestre}/{parcours}', name: 'ressources_annee', requirements: ['annee' => '\d+'])]
    #[Route('/ressources/{annee}/{parcours}', name: 'ressources_annee', requirements: ['annee' => '\d+'])]
    public function ressources(
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Annee $annee, Semestre $semestre = null, ApcParcours $parcours = null): Response
    {
        if ($parcours !== null) {
            $ressources = $apcRessourceParcoursRepository->findByAnneeArray($annee, $parcours);
        } else {
            $ressources = $apcRessourceRepository->findByAnneeArray($annee);
        }

        return $this->render('but/ressources.html.twig', [
            'ressources' => $ressources,
            'annee' => $annee,
            'selectSemestre' => $semestre,
            'parcours' => $parcours
        ]);
    }

    #[Route('/sae/{annee}/{semestre}', name: 'sae_annee_semestre', requirements: ['annee' => '\d+'])]
    #[Route('/sae/{annee}/{semestre}/{parcours}', name: 'sae_annee', requirements: ['annee' => '\d+'])]
    #[Route('/sae/{annee}/{parcours}', name: 'sae_annee', requirements: ['annee' => '\d+'])]
    public function saes(
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcSaeRepository $apcSaeRepository, Annee $annee, Semestre $semestre = null, ApcParcours $parcours = null): Response
    {
        if ($parcours !== null) {
            $saes = $apcSaeParcoursRepository->findByAnneeArray($annee, $parcours);
        } else {
            $saes = $apcSaeRepository->findByAnneeArray($annee);
        }


        return $this->render('but/saes.html.twig', [
            'annee' => $annee,
            'saes' => $saes,
            'parcours' => $parcours,
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
