<?php

namespace App\Controller;

use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\SemestreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/but', name: 'but_')]
class ButController extends BaseController
{
    #[Route('/{annee}', name: 'annee', requirements: ['annee' => '\d+'])]
    public function index(Annee $annee): Response
    {
        //todo: filtrer par année...

        return $this->render('but/index.html.twig', [
            'annee' => $annee
        ]);
    }

    #[Route('/ressources-semestre/{annee}/{semestre}', name: 'ressources_annee_semestre', requirements: ['annee' => '\d+'])]
    #[Route('/ressources/{annee}/{parcours}/{semestre}', name: 'ressources_annee', requirements: ['annee' => '\d+'])]
    #[Route('/ressources-parcours/{annee}/{parcours}', name: 'ressources_annee', requirements: ['annee' => '\d+'])]
    public function ressources(
        SemestreRepository $semestreRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Annee $annee, Semestre $semestre = null, ApcParcours $parcours = null): Response
    {
        if ($parcours !== null) {
            $ressources = $apcRessourceParcoursRepository->findByAnneeArray($annee, $parcours);

        } else {
            $ressources = $apcRessourceRepository->findByAnneeArray($annee);

        }

        if ($this->getDepartement()->getTypeStructure() === Departement::TYPE3 && $parcours !== null ) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        }

        return $this->render('but/ressources.html.twig', [
            'ressources' => $ressources,
            'annee' => $annee,
            'selectSemestre' => $semestre,
            'parcours' => $parcours,
            'semestres' => $semestres
        ]);
    }

    #[Route('/sae-semestre/{annee}/{semestre}', name: 'sae_annee_semestre', requirements: ['annee' => '\d+'])]
    #[Route('/sae/{annee}/{parcours}/{semestre}', name: 'sae_annee', requirements: ['annee' => '\d+'])]
    #[Route('/sae-parcours/{annee}/{parcours}', name: 'sae_annee', requirements: ['annee' => '\d+'])]
    public function saes(
        SemestreRepository $semestreRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcSaeRepository $apcSaeRepository, Annee $annee, Semestre $semestre = null, ApcParcours $parcours = null): Response
    {
        if ($parcours !== null) {
            $saes = $apcSaeParcoursRepository->findByAnneeArray($annee, $parcours);
        } else {
            $saes = $apcSaeRepository->findByAnneeArray($annee);
        }

        if ($this->getDepartement()->getTypeStructure() === Departement::TYPE3 && $parcours !== null ) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        }

        return $this->render('but/saes.html.twig', [
            'annee' => $annee,
            'saes' => $saes,
            'parcours' => $parcours,
            'selectSemestre' => $semestre,
            'semestres' => $semestres
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
