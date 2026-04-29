<?php


namespace App\Controller\Pdf;

use App\Pdf\PdfManager;
use App\Pdf\PdfSourceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class PdfController extends AbstractController
{
    public function __construct(
        private readonly PdfManager $pdfManager,
    )
    {
    }

    #[Route('/admin/pdf/{sourceType}/{sourceId}/{documentKey}', name: 'pdf_show', methods: ['GET'])]
    public function show(
        Request $request,
        string $sourceType,
        string $sourceId,
        string $documentKey = 'fiche'
    ): BinaryFileResponse|JsonResponse|RedirectResponse
    {
        $pdfSourceType = PdfSourceType::from($sourceType);

        $parameters = [];
        $parcoursId = $request->query->get('parcours');
        if (is_string($parcoursId) && $parcoursId !== '') {
            $parameters['parcours'] = $parcoursId;
        }

        if ($request->query->getBoolean('force')) {
            $this->pdfManager->invalidate($pdfSourceType, $sourceId, $documentKey);
        }

        $document = $this->pdfManager->getOrRequest(
            $pdfSourceType,
            $sourceId,
            $documentKey,
            $parameters,
        );

        if ($document->isReady() && $document->getCurrentFilePath() && is_file($document->getCurrentFilePath())) {
            $response = new BinaryFileResponse($document->getCurrentFilePath());
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                sprintf('%s_%s.pdf', $sourceType, $sourceId)
            );

            return $response;
        }

        $job = $this->pdfManager->getLatestJob($pdfSourceType, $sourceId, $documentKey);

        // Les appels AJAX conservent la reponse JSON pour permettre un traitement frontend.
        if ($request->isXmlHttpRequest() || $request->getPreferredFormat() === 'json') {
            return new JsonResponse([
                'status' => $document->getStatus(),
                'message' => 'Le PDF est en cours de génération.',
                'job' => $job ? [
                    'id' => (string)$job->getId(),
                    'status' => $job->getStatus(),
                    'errorMessage' => $job->getErrorMessage(),
                ] : null,
            ], 202);
        }

        $this->addFlash('warning', 'Le PDF est en cours de génération. Réessayez dans quelques instants.');

        return $this->redirect($request->headers->get('referer', '/'));
    }
}
