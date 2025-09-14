<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\QapesCritereRepository;

#[ORM\Entity(repositoryClass: QapesCritereRepository::class)]
class QapesCritere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 200)]
    private ?string $libelle = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $description = null;


    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\QapesSaeCritereReponse>
     */
    #[ORM\OneToMany(targetEntity: QapesSaeCritereReponse::class, mappedBy: 'critere')]
    private \Doctrine\Common\Collections\Collection $qapesSaeCritereReponses;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\QapesCritereReponse>
     */
    #[ORM\OneToMany(targetEntity: QapesCritereReponse::class, mappedBy: 'qapesCritere', cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $qapesCritereReponses;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    private ?string $libelleAffichage = null;

    public function __construct()
    {
        $this->qapesSaeCritereReponses = new ArrayCollection();
        $this->qapesCritereReponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, QapesSaeCritereReponse>
     */
    public function getQapesSaeCritereReponses(): Collection
    {
        return $this->qapesSaeCritereReponses;
    }

    public function addQapesSaeCritereReponse(QapesSaeCritereReponse $qapesSaeCritereReponse): self
    {
        if (!$this->qapesSaeCritereReponses->contains($qapesSaeCritereReponse)) {
            $this->qapesSaeCritereReponses[] = $qapesSaeCritereReponse;
            $qapesSaeCritereReponse->setCritere($this);
        }

        return $this;
    }

    public function removeQapesSaeCritereReponse(QapesSaeCritereReponse $qapesSaeCritereReponse): self
    {
        // set the owning side to null (unless already changed)
        if ($this->qapesSaeCritereReponses->removeElement($qapesSaeCritereReponse) && $qapesSaeCritereReponse->getCritere() === $this) {
            $qapesSaeCritereReponse->setCritere(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, QapesCritereReponse>
     */
    public function getQapesCritereReponses(): Collection
    {
        return $this->qapesCritereReponses;
    }

    public function addQapesCritereReponse(QapesCritereReponse $qapesCritereReponse): self
    {
        if (!$this->qapesCritereReponses->contains($qapesCritereReponse)) {
            $this->qapesCritereReponses[] = $qapesCritereReponse;
            $qapesCritereReponse->setQapesCritere($this);
        }

        return $this;
    }

    public function removeQapesCritereReponse(QapesCritereReponse $qapesCritereReponse): self
    {
        // set the owning side to null (unless already changed)
        if ($this->qapesCritereReponses->removeElement($qapesCritereReponse) && $qapesCritereReponse->getQapesCritere() === $this) {
            $qapesCritereReponse->setQapesCritere(null);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLibelleAffichage(): ?string
    {
        return $this->libelleAffichage;
    }

    public function setLibelleAffichage(?string $libelleAffichage): self
    {
        $this->libelleAffichage = $libelleAffichage;

        return $this;
    }
}
