<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\QapesCritereReponseRepository;

/**
 * @ORM\Entity(repositoryClass=QapesCritereReponseRepository::class)
 */
class QapesCritereReponse
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $couleur;

    /**
     * @ORM\ManyToOne(targetEntity=QapesCritere::class, inversedBy="qapesCritereReponses")
     */
    private $qapesCritere;

    /**
     * @ORM\OneToMany(targetEntity=QapesSaeCritereReponse::class, mappedBy="reponse")
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

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getQapesCritere(): ?QapesCritere
    {
        return $this->qapesCritere;
    }

    public function setQapesCritere(?QapesCritere $qapesCritere): self
    {
        $this->qapesCritere = $qapesCritere;

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
            $qapesSaeCritereReponse->setReponse($this);
        }

        return $this;
    }

    public function removeQapesSaeCritereReponse(QapesSaeCritereReponse $qapesSaeCritereReponse): self
    {
        if ($this->qapesSaeCritereReponses->removeElement($qapesSaeCritereReponse)) {
            // set the owning side to null (unless already changed)
            if ($qapesSaeCritereReponse->getReponse() === $this) {
                $qapesSaeCritereReponse->setReponse(null);
            }
        }

        return $this;
    }
}
