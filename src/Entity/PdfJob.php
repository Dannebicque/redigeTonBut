<?php

namespace App\Entity;

use App\Repository\PdfJobRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PdfJobRepository::class)]
#[ORM\Table(name: 'pdf_job')]
#[ORM\Index(columns: ['source_type', 'source_id', 'document_key'], name: 'idx_pdf_job_identity')]
class PdfJob
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 30)]
    private string $sourceType;

    #[ORM\Column(length: 64)]
    private string $sourceId;

    #[ORM\Column(length: 100)]
    private string $documentKey;

    #[ORM\Column(length: 10)]
    private string $kind;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_QUEUED;

    #[ORM\Column]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $logs = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(nullable: true)]
    private ?string $resultTempUrl = null;

    #[ORM\Column(length: 64)]
    private string $sourceHash;

    public function __construct(
        string $sourceType,
        string $sourceId,
        string $documentKey,
        string $kind,
        string $sourceHash,
    ) {
        $this->id = Uuid::v7();
        $this->sourceType = $sourceType;
        $this->sourceId = $sourceId;
        $this->documentKey = $documentKey;
        $this->kind = $kind;
        $this->sourceHash = $sourceHash;
        $this->requestedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    public function getDocumentKey(): string
    {
        return $this->documentKey;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLogs(): ?string
    {
        return $this->logs;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getResultTempUrl(): ?string
    {
        return $this->resultTempUrl;
    }

    public function getSourceHash(): string
    {
        return $this->sourceHash;
    }

    public function markSuccess(?string $tempUrl, ?string $logs): void
    {
        $this->status = self::STATUS_SUCCESS;
        $this->resultTempUrl = $tempUrl;
        $this->logs = $logs;
        $this->finishedAt = new \DateTimeImmutable();
    }

    public function markError(?string $message, ?string $logs): void
    {
        $this->status = self::STATUS_ERROR;
        $this->errorMessage = $message;
        $this->logs = $logs;
        $this->finishedAt = new \DateTimeImmutable();
    }
}
