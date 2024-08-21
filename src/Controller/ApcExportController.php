<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller;

use App\Classes\Export\AllDepartementsExport;
use App\Classes\Export\DepartementExport;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route("/apc/export/referentiel", name:"administration_apc_referentiel_")]
class ApcExportController extends BaseController
{
    #[Route("/competences", name:"competence_export")]
    public function exportCompetences(
        DepartementExport $departementExport
    ): Response {
        return $departementExport->exportRefentiel($this->getDepartement());
    }

    #[Route("/competences/all", name:"competence_export_all")]
    public function exportCompetencesAll(
        AllDepartementsExport $departementExport
    ): Response {
        return $departementExport->exportCompetences();
    }

    #[Route('/formation', name: 'formation_export')]
    public function exportFormation(
        DepartementExport $departementExport
    ): Response {
        return $departementExport->exportProgramme($this->getDepartement());
    }

    #[Route('/', name: 'export')]
    public function export(
        DepartementRepository $departementRepository
    ): Response {
        if (! $this->isGranted('ROLE_GT')) {
            throw new AccessDeniedException();
        }

        return $this->render(
            'apc_export/index.html.twig', [
                'departements' => $departementRepository->findAll()
            ]
        );
    }
}
