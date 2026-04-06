<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArgumentaireRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArgumentaireRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_argumentaire_version', columns: ['version_id'])]
class Argumentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Version $version = null;

    /**
     * @var array<string, string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $payload = [];

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function setVersion(Version $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array<string, string> $payload
     */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): self
    {
        $this->updatedAt = new DateTimeImmutable();

        return $this;
    }
}

