<?php

namespace App\Controller;

use App\Entity\ApcParcours;
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
    private const DOCUMENT_KEY_PARCOURS = 'export_latex_parcours';
    private const DOCUMENT_KEY_TRONC_COMMUN = 'export_latex_tronc_commun';
    private const DOCUMENT_KEY_PARCOURS_PREFIX = 'export_latex_parcours_';
    private const DOCUMENT_KEY_ADAPTATION_LOCALE_PREFIX = 'export_latex_al_';

    public function __construct(
        private readonly PdfManager $pdfManager,
    ) {
    }

    #[Route('/export/pdf/parcours', name: 'export_pdf_parcours')]
    public function parcours(
        Request $request,
    ): BinaryFileResponse|JsonResponse|RedirectResponse {
        return $this->handleDeferredReferentielExport(
            $request,
            self::DOCUMENT_KEY_PARCOURS,
            ['scope' => 'parcours'],
        );
    }

    #[Route('/export/pdf/tronc-commun', name: 'export_pdf_tronc_commun')]
    public function troncCommun(
        Request $request,
    ): BinaryFileResponse|JsonResponse|RedirectResponse {
        return $this->handleDeferredReferentielExport(
            $request,
            self::DOCUMENT_KEY_TRONC_COMMUN,
            ['scope' => 'tronc_commun'],
        );
    }

    #[Route('/export/pdf/{parcours}', name: 'export_pdf')]
    public function index(
        Request $request,
        ApcParcours $parcours
    ): BinaryFileResponse|JsonResponse|RedirectResponse {
        return $this->handleDeferredReferentielExport(
            $request,
            self::DOCUMENT_KEY_PARCOURS_PREFIX . $parcours->getId(),
            ['parcours' => (string) $parcours->getId(), 'adaptationLocale' => false],
        );
    }

    #[Route('/export/al/pdf/{parcours}', name: 'export_pdf_adaptation_locale')]
    public function exportPdfAl(
        Request $request,
        ApcParcours $parcours
    ): BinaryFileResponse|JsonResponse|RedirectResponse {
        return $this->handleDeferredReferentielExport(
            $request,
            self::DOCUMENT_KEY_ADAPTATION_LOCALE_PREFIX . $parcours->getId(),
            ['parcours' => (string) $parcours->getId(), 'adaptationLocale' => true],
        );
    }

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
                sprintf('referentiel_%s_%s.pdf', $sourceId, $documentKey)
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
