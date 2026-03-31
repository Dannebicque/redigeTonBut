<?php

namespace App\Controller\Pdf;

use App\Repository\PdfDocumentRepository;
use App\Repository\PdfJobRepository;
use App\Pdf\HmacSigner;
use App\Pdf\PdfStorage;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PdfCallbackController extends AbstractController
{
    private const PROBE_LOG_FILE = '/var/log/pdf_callback.probe.log';

    public function __construct(
        private readonly string $callbackBearer,
        private readonly HmacSigner $signer,
        private readonly PdfJobRepository $pdfJobRepository,
        private readonly PdfDocumentRepository $pdfDocumentRepository,
        private readonly PdfStorage $storage,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/pdf/callback', name: 'pdf_callback', methods: ['POST'])]
    public function __invoke(
        Request $request): Response
    {
        $startedAt = microtime(true);
        $probeId = bin2hex(random_bytes(4));
        $this->writeProbe('callback_reached', $probeId, $request, null);

        $this->logger->info('PDF callback received', [
            'route' => 'pdf_callback',
            'method' => $request->getMethod(),
            'content_length' => strlen($request->getContent()),
        ]);

//        if ($request->headers->get('Authorization') !== 'Bearer '.$this->callbackBearer) {
//            return new Response('Unauthorized', 401);
//        }

        $raw = $request->getContent();

//        if (!$this->signer->isValid($raw, $request->headers->get('X-Signature'))) {
//            return new Response('Bad signature', 400);
//        }

        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $this->writeProbe('bad_json', $probeId, $request, null);
            $this->logger->warning('PDF callback bad JSON payload');
            return new Response('Bad JSON', 400);
        }

        $jobId = $data['jobId'] ?? null;
        $status = $data['status'] ?? null;
        $tempPdfUrl = $data['tempPdfUrl'] ?? null;
        $logs = $data['logs'] ?? null;
        $errorMessage = $data['errorMessage'] ?? null;
        $filename = $data['filename'] ?? 'document.pdf';

        if (!is_string($jobId) || !is_string($status)) {
            $this->writeProbe('missing_fields', $probeId, $request, [
                'jobId_type' => gettype($jobId),
                'status_type' => gettype($status),
            ]);
            $this->logger->warning('PDF callback missing required fields', [
                'jobId_type' => gettype($jobId),
                'status_type' => gettype($status),
                'payload_keys' => array_keys($data),
            ]);
            return new Response('Missing fields', 400);
        }

        $this->logger->info('PDF callback payload parsed', [
            'jobId' => $jobId,
            'status' => $status,
            'probeId' => $probeId,
        ]);

        $job = $this->pdfJobRepository->findByUuid($jobId);

        if (!$job) {
            $this->logger->warning('PDF callback job not found', ['jobId' => $jobId]);
            return new Response('Job not found', 404);
        }

        $document = $this->pdfDocumentRepository->findOneByIdentity(
            $job->getSourceType(),
            $job->getSourceId(),
            $job->getDocumentKey()
        );

        if (!$document) {
            $this->logger->warning('PDF callback document not found', [
                'jobId' => $jobId,
                'sourceType' => $job->getSourceType(),
                'sourceId' => $job->getSourceId(),
                'documentKey' => $job->getDocumentKey(),
            ]);
            return new Response('Document not found', 404);
        }

        if ($status !== 'success') {
            $this->logger->warning('PDF callback received error status', [
                'jobId' => $jobId,
                'status' => $status,
                'errorMessage' => is_string($errorMessage) ? $errorMessage : null,
            ]);

            $this->entityManager->wrapInTransaction(function () use ($document, $job, $errorMessage, $logs) {
                $this->entityManager->lock($document, LockMode::PESSIMISTIC_WRITE);
                $job->markError(is_string($errorMessage) ? $errorMessage : 'Erreur de génération PDF', is_string($logs) ? $logs : null);
                $document->markError();
                $this->entityManager->flush();
            });

            $this->logger->info('PDF callback error status persisted', [
                'jobId' => $jobId,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return new Response('OK', 200);
        }

        if (!is_string($tempPdfUrl) || $tempPdfUrl === '') {
            $this->logger->warning('PDF callback missing tempPdfUrl', ['jobId' => $jobId]);
            return new Response('Missing tempPdfUrl', 400);
        }

        $newPath = $this->storage->createNewPath(
            $job->getSourceType(),
            $job->getSourceId(),
            $job->getDocumentKey(),
            is_string($filename) ? $filename : 'document.pdf'
        );

        $this->logger->info('PDF callback starts temporary file download', [
            'jobId' => $jobId,
            'targetPath' => $newPath,
        ]);

        $response = $this->httpClient->request('GET', $tempPdfUrl, ['timeout' => 300]);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->logger->error('PDF callback temporary file download failed', [
                'jobId' => $jobId,
                'http_status' => $response->getStatusCode(),
            ]);
            return new Response('Unable to download temporary PDF', 400);
        }

        $stream = fopen($newPath, 'wb');
        if ($stream === false) {
            $this->logger->error('PDF callback cannot open destination file', [
                'jobId' => $jobId,
                'targetPath' => $newPath,
            ]);
            return new Response('Unable to write file', 500);
        }

        $bytesWritten = 0;

        try {
            foreach ($this->httpClient->stream($response) as $chunk) {
                if ($chunk->isTimeout()) {
                    $this->logger->error('PDF callback timeout during stream download', ['jobId' => $jobId]);
                    throw new \RuntimeException('Timeout during PDF download');
                }

                $content = $chunk->getContent();
                if ($content !== '') {
                    $written = fwrite($stream, $content);
                    if ($written === false) {
                        $this->logger->error('PDF callback failed while writing stream chunk', ['jobId' => $jobId]);
                        throw new \RuntimeException('Unable to write stream chunk');
                    }
                    $bytesWritten += $written;
                }
            }
        } finally {
            fclose($stream);
        }

        $sha256 = hash_file('sha256', $newPath);
        if ($sha256 === false) {
            $this->logger->error('PDF callback unable to compute file hash', [
                'jobId' => $jobId,
                'targetPath' => $newPath,
            ]);
            @unlink($newPath);
            return new Response('Hash error', 500);
        }

        $oldPath = $document->getCurrentFilePath();

        $this->entityManager->wrapInTransaction(function () use ($document, $job, $newPath, $sha256, $oldPath, $logs) {
            $this->entityManager->lock($document, LockMode::PESSIMISTIC_WRITE);

            $job->markSuccess($job->getResultTempUrl(), is_string($logs) ? $logs : null);
            $document->markReady($newPath, $sha256, $job->getSourceHash());

            $this->entityManager->flush();
        });

        if ($oldPath && $oldPath !== $newPath) {
            $this->storage->deleteIfExists($oldPath);
        }

        $this->logger->info('PDF callback processed successfully', [
            'jobId' => $jobId,
            'bytes' => $bytesWritten,
            'sha256_prefix' => substr($sha256, 0, 12),
            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
        ]);

        return new Response('OK', 200);
    }

    private function writeProbe(string $step, string $probeId, Request $request, ?array $context): void
    {
        $line = json_encode([
            'ts' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'probeId' => $probeId,
            'step' => $step,
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'remote' => $request->getClientIp(),
            'context' => $context,
        ], JSON_UNESCAPED_SLASHES);

        if ($line === false) {
            return;
        }

        @file_put_contents(
            $this->getParameter('kernel.project_dir').self::PROBE_LOG_FILE,
            $line.PHP_EOL,
            FILE_APPEND
        );
    }
}
