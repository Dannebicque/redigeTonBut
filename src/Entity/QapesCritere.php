<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\QapesCritereRepository;

/**
 * @ORM\Entity(repositoryClass=QapesCritereRepository::class)
 */
class QapesCritere
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $libelle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;


    /**
     * @ORM\OneToMany(targetEntity=QapesSaeCritereReponse::class, mappedBy="critere")
     */
    private $qapesSaeCritereReponses;

    /**
     * @ORM\OneToMany(targetEntity=QapesCritereReponse::class, mappedBy="qapesCritere", cascade={"persist","remove"})
     */
    private $qapesCritereReponses;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $libelleAffichage;

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
        if ($this->qapesSaeCritereReponses->removeElement($qapesSaeCritereReponse)) {
            // set the owning side to null (unless already changed)
            if ($qapesSaeCritereReponse->getCritere() === $this) {
                $qapesSaeCritereReponse->setCritere(null);
            }
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
        if ($this->qapesCritereReponses->removeElement($qapesCritereReponse)) {
            // set the owning side to null (unless already changed)
            if ($qapesCritereReponse->getQapesCritere() === $this) {
                $qapesCritereReponse->setQapesCritere(null);
            }
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
