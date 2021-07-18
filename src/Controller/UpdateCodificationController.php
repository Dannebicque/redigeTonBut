<?php

namespace App\Controller;

use App\Entity\Semestre;
use App\Repository\ApcApprentissageCritiqueRepository;
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

    #[Route('/ressources/{semestre}', name: 'ressources')]
    public function ressources(Semestre $semestre): Response
    {
        foreach ($semestre->getApcRessources() as $ressource) {
            $ressource->setCodeMatiere(Codification::codeRessource($ressource));
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('update_codification_index');
    }

    #[Route('/sae/{semestre}', name: 'sae')]
    public function sae(Semestre $semestre): Response
    {
        foreach ($semestre->getApcSaes() as $sae) {
            $sae->setCodeMatiere(Codification::codeSae($sae));
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
