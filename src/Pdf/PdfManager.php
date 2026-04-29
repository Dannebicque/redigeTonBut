<?php

namespace App\Pdf;

use App\Entity\PdfDocument;
use App\Entity\PdfJob;
use App\Repository\PdfDocumentRepository;
use App\Repository\PdfJobRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PdfManager
{
    public const DISPLAY_STATUS_PRESENT = 'present';
    public const DISPLAY_STATUS_PENDING = 'pending';
    public const DISPLAY_STATUS_ERROR = 'error';
    public const DISPLAY_STATUS_ABSENT = 'absent';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PdfDocumentRepository $pdfDocumentRepository,
        private readonly PdfJobRepository $pdfJobRepository,
        private readonly PdfParametersNormalizer $normalizer,
        private readonly PdfPayloadBuilderRegistry $builderRegistry,
        private readonly PdfRemoteClient $remoteClient,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getOrRequest(
        PdfSourceType $sourceType,
        string $sourceId,
        string $documentKey = 'fiche',
        array $parameters = [],
    ): PdfDocument {
        $normalizedParameters = $this->normalizer->normalize($parameters);
        $parametersHash = $this->normalizer->hash($normalizedParameters);

        return $this->entityManager->wrapInTransaction(function () use (
            $sourceType,
            $sourceId,
            $documentKey,
            $normalizedParameters,
            $parametersHash
        ) {
            $documents = $this->pdfDocumentRepository->findAllByIdentity(
                $sourceType->value,
                $sourceId,
                $documentKey
            );

            $document = $this->selectCanonicalDocument($documents);

            if (!$document) {
                $document = new PdfDocument(
                    $sourceType->value,
                    $sourceId,
                    $documentKey,
                    $normalizedParameters,
                    $parametersHash,
                );
                $this->entityManager->persist($document);
                $this->entityManager->flush();
                $documents = [$document];
            }

            if (count($documents) > 1) {
                $this->removeDuplicateDocuments($documents, $document);
                $this->entityManager->flush();
            }

            $this->entityManager->lock($document, LockMode::PESSIMISTIC_WRITE);

            $sameParameters = $document->getParametersHash() === $parametersHash;

            // Fast-path: document deja pret pour la meme variante de parametres.
            if (
                $sameParameters
                && $document->isReady()
                && $document->getCurrentFilePath()
                && is_file($document->getCurrentFilePath())
            ) {
                return $document;
            }

            if (!$sameParameters) {
                $document->syncParameters($normalizedParameters, $parametersHash);
                $this->entityManager->flush();
            }

            if ($document->getStatus() === PdfDocument::STATUS_GENERATING) {
                return $document;
            }

            $builder = $this->builderRegistry->getBuilder($sourceType, $documentKey);
            $remoteRequest = $builder->build($sourceId, $documentKey, $normalizedParameters);

            if (
                $document->isReady()
                && $document->getSourceHash() === $remoteRequest->sourceHash
                && $document->getCurrentFilePath()
                && is_file($document->getCurrentFilePath())
            ) {
                return $document;
            }

            $document->markGenerating();

            $job = new PdfJob(
                $sourceType->value,
                $sourceId,
                $documentKey,
                $remoteRequest->type,
                $remoteRequest->sourceHash
            );

            $this->entityManager->persist($job);
            $this->entityManager->flush();

            $cacheKey = sprintf('%s:%s:%s', $sourceType->value, $sourceId, $documentKey);

            $callbackUrl = $this->urlGenerator->generate(
                'pdf_callback',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $apiPayload = $remoteRequest->toApiPayload(
                jobId: (string) $job->getId(),
                cacheKey: $cacheKey,
                callbackUrl: $callbackUrl,
            );

//            dd($apiPayload);
            $this->remoteClient->createJob($apiPayload);

            return $document;
        });
    }

    public function invalidate(
        PdfSourceType $sourceType,
        string $sourceId,
        ?string $documentKey = null,
    ): void {
        if ($documentKey !== null) {
            $documents = $this->pdfDocumentRepository->findAllByIdentity(
                $sourceType->value,
                $sourceId,
                $documentKey
            );

            foreach ($documents as $document) {
                $document->invalidate();
            }

            $this->entityManager->flush();

            return;
        }

        $documents = $this->pdfDocumentRepository->findBy([
            'sourceType' => $sourceType->value,
            'sourceId' => $sourceId,
        ]);

        foreach ($documents as $document) {
            $document->invalidate();
        }

        $this->entityManager->flush();
    }

    public function getLatestJob(
        PdfSourceType $sourceType,
        string $sourceId,
        string $documentKey
    ): ?PdfJob {
        return $this->pdfJobRepository->findLatestFor(
            $sourceType->value,
            $sourceId,
            $documentKey
        );
    }

    /**
     * @param string[] $sourceIds
     *
     * @return array<string, array{status: string, errorMessage: ?string, lastGeneratedAt: ?\DateTimeImmutable}>
     */
    public function getDisplayStatusesForSources(
        PdfSourceType $sourceType,
        array $sourceIds,
        string $documentKey = 'fiche'
    ): array {
        $normalizedIds = array_values(array_unique(array_map(static fn ($id) => (string) $id, $sourceIds)));
        if (count($normalizedIds) === 0) {
            return [];
        }

        $documents = $this->pdfDocumentRepository->findByIdentities($sourceType->value, $normalizedIds, $documentKey);
        $jobsBySourceId = $this->pdfJobRepository->findLatestForSources($sourceType->value, $normalizedIds, $documentKey);

        $documentsBySourceId = [];
        foreach ($documents as $document) {
            $documentsBySourceId[$document->getSourceId()] = $document;
        }

        $statuses = [];
        foreach ($normalizedIds as $sourceId) {
            $document = $documentsBySourceId[$sourceId] ?? null;
            $job = $jobsBySourceId[$sourceId] ?? null;

            if ($document && $document->isReady() && $document->getCurrentFilePath() && is_file($document->getCurrentFilePath())) {
                $statuses[$sourceId] = [
                    'status' => self::DISPLAY_STATUS_PRESENT,
                    'errorMessage' => null,
                    'lastGeneratedAt' => $document->getUpdatedAt(),
                ];
                continue;
            }

            if (
                ($document && $document->getStatus() === PdfDocument::STATUS_ERROR)
                || ($job && $job->getStatus() === PdfJob::STATUS_ERROR)
            ) {
                $statuses[$sourceId] = [
                    'status' => self::DISPLAY_STATUS_ERROR,
                    'errorMessage' => $job?->getErrorMessage(),
                    'lastGeneratedAt' => null,
                ];
                continue;
            }

            if (
                ($document && $document->getStatus() === PdfDocument::STATUS_GENERATING)
                || ($job && $job->getStatus() === PdfJob::STATUS_QUEUED)
            ) {
                $statuses[$sourceId] = [
                    'status' => self::DISPLAY_STATUS_PENDING,
                    'errorMessage' => null,
                    'lastGeneratedAt' => null,
                ];
                continue;
            }

            $statuses[$sourceId] = [
                'status' => self::DISPLAY_STATUS_ABSENT,
                'errorMessage' => null,
                'lastGeneratedAt' => null,
            ];
        }

        return $statuses;
    }

    /**
     * @param PdfDocument[] $documents
     */
    private function selectCanonicalDocument(array $documents): ?PdfDocument
    {
        if (count($documents) === 0) {
            return null;
        }

        foreach ($documents as $document) {
            if ($document->isReady() && $document->getCurrentFilePath() && is_file($document->getCurrentFilePath())) {
                return $document;
            }
        }

        return $documents[0];
    }

    /**
     * @param PdfDocument[] $documents
     */
    private function removeDuplicateDocuments(array $documents, PdfDocument $documentToKeep): void
    {
        foreach ($documents as $document) {
            if ($document === $documentToKeep) {
                continue;
            }

            $this->entityManager->remove($document);
        }
    }
}
