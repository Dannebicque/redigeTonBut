<?php

namespace App\Controller;

use App\Entity\Departement;
use App\Entity\Version;
use App\Pdf\Builder\ReferentielPdfPayloadBuilder;
use App\Pdf\PdfManager;
use App\Pdf\PdfSourceType;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_GT')]
class GtController extends BaseController
{
    public function __construct(
        private readonly PdfManager $pdfManager
    ) {
    }

    #[Route('/gt', name: 'gt_index')]
    public function index(
        DepartementRepository $departementRepository
    ): Response
    {
        $departements = $departementRepository->findAll();
        $currentDepartement = $this->getDepartement();

        $parcours = [];
        if ($currentDepartement instanceof Departement) {
            $version = $this->getVersion();
            if ($version) {
                $parcours = $version->getApcParcours();
            }
        } else {
            $currentDepartement = null;
        }

        return $this->render('gt/index.html.twig', [
            'departements' => $departements,
            'currentDepartement' => $currentDepartement ,
            'pdfReferentielStatuses' => $this->buildReferentielPdfStatuses($version),
            'parcours' => $parcours,
        ]);
    }

    private function buildReferentielPdfStatuses(?Version $version): array
    {
        $defaultStatus = [
            'status' => PdfManager::DISPLAY_STATUS_ABSENT,
            'errorMessage' => null,
            'lastGeneratedAt' => null,
        ];

        if ($version === null || $version->getId() === null) {
            return [
                'complete' => $defaultStatus,
            ];
        }

        $sourceId = (string) $version->getId();

        $statuses = $this->pdfManager->getDisplayStatusesForSources(
            PdfSourceType::REFERENTIEL,
            [$sourceId],
            ReferentielPdfPayloadBuilder::DOCUMENT_KEY_COMPLETE,
        );

        return [
            'complete' => $statuses[$sourceId] ?? $defaultStatus,
        ];
    }
}
