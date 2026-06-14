<?php

namespace App\Controller;

use App\Pdf\Builder\ReferentielPdfPayloadBuilder;
use App\Pdf\PdfManager;
use App\Pdf\PdfSourceType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class ExportPdfController extends BaseController
{
    public function __construct(
        private readonly PdfManager $pdfManager,
    ) {
    }

    #[Route('/export/pdf', name: 'export_pdf')]
    public function index(
        Request $request,
    ): BinaryFileResponse|JsonResponse|RedirectResponse {
        return $this->handleDeferredReferentielExport(
            $request,
            ReferentielPdfPayloadBuilder::DOCUMENT_KEY_COMPLETE,
            ['includeAssets' => false],
        );
    }

//    #[Route('/export/pdf/tronc-commun', name: 'export_pdf_tronc_commun')]
//    public function troncCommun(
//        Request $request,
//    ): BinaryFileResponse|JsonResponse|RedirectResponse {
//        return $this->handleDeferredReferentielExport(
//            $request,
//            ReferentielPdfPayloadBuilder::DOCUMENT_KEY_COMPLETE,
//            ['includeAssets' => false],
//        );
//    }

//    #[Route('/export/pdf/parcours', name: 'export_pdf_parcours')]
//    public function parcours(
//        Request $request,
//    ): BinaryFileResponse|JsonResponse|RedirectResponse {
//        return $this->handleDeferredReferentielExport(
//            $request,
//            ReferentielPdfPayloadBuilder::DOCUMENT_KEY_COMPLETE,
//            ['includeAssets' => false],
//        );
//    }

//    #[Route('/export/pdf/{parcours}', name: 'export_pdf_legacy_parcours')]
//    public function legacyParcours(
//        Request $request,
//        ApcParcours $parcours
//    ): BinaryFileResponse|JsonResponse|RedirectResponse {
//        unset($parcours);
//
//        return $this->handleDeferredReferentielExport(
//            $request,
//            ReferentielPdfPayloadBuilder::DOCUMENT_KEY_COMPLETE,
//            ['includeAssets' => false],
//        );
//    }

//    #[Route('/export/al/pdf/{parcours}', name: 'export_pdf_adaptation_locale')]
//    public function exportPdfAl(
//        Request $request,
//        ApcParcours $parcours
//    ): BinaryFileResponse|JsonResponse|RedirectResponse {
//        unset($parcours);
//
//        return $this->handleDeferredReferentielExport(
//            $request,
//            ReferentielPdfPayloadBuilder::DOCUMENT_KEY_COMPLETE,
//            ['includeAssets' => false],
//        );
//    }

    private function handleDeferredReferentielExport(
        Request $request,
        string $documentKey,
        array $parameters = [],
    ): BinaryFileResponse|JsonResponse|RedirectResponse {
        $version = $this->getVersion();
        if ($version === null || $version->getId() === null) {
            throw $this->createNotFoundException('Version active introuvable.');
        }

        $sourceId = (string) $version->getId();

        if ($request->query->getBoolean('force')) {
            $this->pdfManager->invalidate(PdfSourceType::REFERENTIEL, $sourceId, $documentKey);
        }

        $document = $this->pdfManager->getOrRequest(
            PdfSourceType::REFERENTIEL,
            $sourceId,
            $documentKey,
            $parameters,
        );

        if ($document->isReady() && $document->getCurrentFilePath() && is_file($document->getCurrentFilePath())) {
            $response = new BinaryFileResponse($document->getCurrentFilePath());
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                sprintf('referentiel_%s_%s.pdf', $version->getDepartement()->getSigle(), $documentKey)
            );

            return $response;
        }

        $job = $this->pdfManager->getLatestJob(PdfSourceType::REFERENTIEL, $sourceId, $documentKey);

        if ($request->isXmlHttpRequest() || $request->getPreferredFormat() === 'json') {
            return new JsonResponse([
                'status' => $document->getStatus(),
                'message' => 'Le PDF est en cours de génération.',
                'job' => $job ? [
                    'id' => (string) $job->getId(),
                    'status' => $job->getStatus(),
                    'errorMessage' => $job->getErrorMessage(),
                ] : null,
            ], 202);
        }

        $this->addFlash('warning', 'Le document est en cours de génération. Réessayez dans quelques instants.');

        return $this->redirect($request->headers->get('referer', '/'));
    }
}
