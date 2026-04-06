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
use App\Repository\VersionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route("/apc/export/referentiel", name: "administration_apc_referentiel_")]
class ApcExportController extends BaseController
{
    #[Route("/competences", name: "competence_export")]
    public function exportCompetences(
        DepartementExport $departementExport
    ): Response
    {
        return $departementExport->exportRefentiel($this->getVersion());
    }

    #[Route("/competences/all", name: "competence_export_all")]
    public function exportCompetencesAll(
        AllDepartementsExport $departementExport
    ): Response
    {
        return $departementExport->exportCompetences($this->getVersion());
    }

    #[Route('/formation', name: 'formation_export')]
    public function exportFormation(
        DepartementExport $departementExport
    ): Response
    {
        return $departementExport->exportProgramme($this->getVersion());
    }

    #[Route('/', name: 'export', methods: ['GET'])]
    public function export(
        VersionRepository $versionRepository
    ): Response
    {
        if (!($this->isGranted('ROLE_CPN') || $this->isGranted('ROLE_PACD'))) {
            throw new AccessDeniedException();
        }

        return $this->render(
            'apc_export/index.html.twig', [
                'departements' =>
                    $this->isGranted('ROLE_GT') ? $versionRepository->findAll() : $versionRepository->findByUser($this->getUser())
            ]
        );
    }

    #[Route('/', name: 'export_post', methods: ['POST'])]
    public function exportPost(
        Request           $request,
        DepartementExport $departementExport,
        VersionRepository $versionRepository
    ): Response
    {
        if (!($this->isGranted('ROLE_CPN') || $this->isGranted('ROLE_PACD'))) {
            throw new AccessDeniedException();
        }

        $version = $request->request->get('departement');
        $typeExport = $request->request->get('typeExport');
        $typeFichier = $request->request->get('typeFichier');
        $version = $versionRepository->find($version);
        if ($version === null) {
            throw $this->createNotFoundException('Département introuvable');
        }

        if ($typeExport === 'competences') {
            if ($typeFichier === 'xml') {
                return $departementExport->exportRefentiel($version);
            }

        } elseif ($typeExport === 'formation') {
            if ($typeFichier === 'xml') {
                return $departementExport->exportProgramme($version);
            }
        }
        $this->addFlashBag('error', 'Type d\'export ou type de fichier non pris en charge');
        return $this->redirectToRoute('administration_apc_referentiel_export');

    }
}
