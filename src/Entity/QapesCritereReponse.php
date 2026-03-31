<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\QapesCritereReponseRepository;

#[ORM\Entity(repositoryClass: QapesCritereReponseRepository::class)]
class QapesCritereReponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 150)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::STRING, length: 7)]
    private ?string $couleur = null;

    #[ORM\ManyToOne(targetEntity: QapesCritere::class, inversedBy: 'qapesCritereReponses')]
    private ?QapesCritere $qapesCritere = null;

    /**
     * @var Collection<int, QapesSaeCritereReponse>
     */
    #[ORM\OneToMany(mappedBy: 'reponse', targetEntity: QapesSaeCritereReponse::class)]
    private Collection $qapesSaeCritereReponses;

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
        // set the owning side to null (unless already changed)
        if ($this->qapesSaeCritereReponses->removeElement($qapesSaeCritereReponse) && $qapesSaeCritereReponse->getReponse() === $this) {
            $qapesSaeCritereReponse->setReponse(null);
        }

        return $this;
    }
}
