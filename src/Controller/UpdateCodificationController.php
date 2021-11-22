<?php

namespace App\Controller;

use App\Entity\ApcParcours;
use App\Entity\Semestre;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Utils\Codification;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/update/codification', name: 'update_codification_')]
class UpdateCodificationController extends BaseController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('update_codification/index.html.twig', [
            'semestres' => $this->getDepartement()->getSemestres()
        ]);
    }

    #[Route('/ressources/{semestre}/{parcours}', name: 'ressources')]
    public function ressources(
        ApcRessourceRepository $apcRessourceRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        Semestre $semestre, ApcParcours $parcours = null): Response
    {
        if ($parcours === null) {
            $ressources = $apcRessourceRepository->findBySemestre($semestre);
        } else {
            $ressources = $apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
        }


        foreach ($ressources as $ressource) {
            $ressource->setCodeMatiere(Codification::codeRessource($ressource, $ressource->getApcRessourceParcours()));
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('update_codification_index');
    }

    #[Route('/sae/{semestre}/{parcours}', name: 'sae')]
    public function sae(
        ApcSaeRepository $apcSaeRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        Semestre $semestre, ApcParcours $parcours = null): Response
    {
        if ($parcours === null) {
            $saes = $apcSaeRepository->findBySemestre($semestre);
        } else {
            $saes = $apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
        }

        foreach ($saes as $sae) {
            $sae->setCodeMatiere(Codification::codeSae($sae, $sae->getApcSaeParcours()));
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('update_codification_index');
    }

    #[Route('/apprentissages-critiques', name: 'acs')]
    public function acs(ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository): Response
    {
        $acs = $apcApprentissageCritiqueRepository->findByDepartement($this->getDepartement());
        foreach ($this->getDepartement()->getApcCompetences() as $competence)
        {
            $competence->setNumero(substr($competence->getCouleur(),1,1));
        }

        foreach ($acs as $ac)
        {
            $ac->setCode(Codification::codeApprentissageCritique($ac));
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('update_codification_index');
    }
}
