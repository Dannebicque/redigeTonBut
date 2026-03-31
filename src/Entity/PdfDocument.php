<?php

namespace App\Entity;

use App\Repository\PdfDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PdfDocumentRepository::class)]
#[ORM\Table(name: 'pdf_document')]
#[ORM\UniqueConstraint(name: 'uniq_pdf_document_identity', columns: ['source_type', 'source_id', 'document_key'])]
class PdfDocument
{
    public const STATUS_READY = 'ready';
    public const STATUS_GENERATING = 'generating';
    public const STATUS_ERROR = 'error';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 30)]
    private string $sourceType;

    #[ORM\Column(length: 64)]
    private string $sourceId;

    /**
     * Permet de distinguer plusieurs PDFs pour une même ressource.
     * Exemple: "fiche", "programme", "export_complet"
     */
    #[ORM\Column(length: 100)]
    private string $documentKey;

    #[ORM\Column(type: 'json')]
    private array $parameters = [];

    #[ORM\Column(length: 64)]
    private string $parametersHash;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_READY;

    #[ORM\Column(nullable: true)]
    private ?string $currentFilePath = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $currentFileSha256 = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $sourceHash = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $invalidatedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $sourceType,
        string $sourceId,
        string $documentKey,
        array $parameters,
        string $parametersHash,
    ) {
        $this->id = Uuid::v7();
        $this->sourceType = $sourceType;
        $this->sourceId = $sourceId;
        $this->documentKey = $documentKey;
        $this->parameters = $parameters;
        $this->parametersHash = $parametersHash;
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParametersHash(): string
    {
        return $this->parametersHash;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCurrentFilePath(): ?string
    {
        return $this->currentFilePath;
    }

    public function getCurrentFileSha256(): ?string
    {
        return $this->currentFileSha256;
    }

    public function getSourceHash(): ?string
    {
        return $this->sourceHash;
    }

    public function getInvalidatedAt(): ?\DateTimeImmutable
    {
        return $this->invalidatedAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY
            && $this->currentFilePath !== null
            && $this->invalidatedAt === null;
    }

    public function markGenerating(): void
    {
        $this->status = self::STATUS_GENERATING;
        $this->touch();
    }

    public function markReady(string $filePath, string $sha256, string $sourceHash): void
    {
        $this->status = self::STATUS_READY;
        $this->currentFilePath = $filePath;
        $this->currentFileSha256 = $sha256;
        $this->sourceHash = $sourceHash;
        $this->invalidatedAt = null;
        $this->touch();
    }

    public function markError(): void
    {
        $this->status = self::STATUS_ERROR;
        $this->touch();
    }

    public function invalidate(): void
    {
        $this->invalidatedAt = new \DateTimeImmutable();
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
