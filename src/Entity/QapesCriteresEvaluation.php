<?php

namespace App\Entity;

use App\Repository\QapesCriteresEvaluationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QapesCriteresEvaluationRepository::class)
 */
class QapesCriteresEvaluation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="text")
     */
    private $valeurs = '';

    /**
     * @ORM\OneToMany(targetEntity=QapesSaeCritereReponse::class, mappedBy="qapes_critere")
     */
    private $qapesSaeCritereReponses;

    public function __construct()
    {
        $this->qapesSaeCritereReponses = new ArrayCollection();
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

    public function getValeurs(): ?string
    {
        return $this->valeurs;
    }

    public function setValeurs(string $valeurs): self
    {
        $this->valeurs = $valeurs;

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
            $qapesSaeCritereReponse->setQapesCritere($this);
        }

        return $this;
    }

    public function removeQapesSaeCritereReponse(QapesSaeCritereReponse $qapesSaeCritereReponse): self
    {
        if ($this->qapesSaeCritereReponses->removeElement($qapesSaeCritereReponse)) {
            // set the owning side to null (unless already changed)
            if ($qapesSaeCritereReponse->getQapesCritere() === $this) {
                $qapesSaeCritereReponse->setQapesCritere(null);
            }
        }

        return $this;
    }
}
