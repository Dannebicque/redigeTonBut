<?php

namespace App\Entity;

use App\Repository\IutUniversiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IutUniversiteRepository::class)]
class IutUniversite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(targetEntity: IutAcademie::class, inversedBy: 'iutUniversites')]
    private ?IutAcademie $academie = null;

    /**
     * @var Collection<int, Iut>
     */
    #[ORM\OneToMany(mappedBy: 'universite', targetEntity: Iut::class)]
    private Collection $iuts;

    public function __construct()
    {
        $this->iuts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->libelle;
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

    public function getAcademie(): ?IutAcademie
    {
        return $this->academie;
    }

    public function setAcademie(?IutAcademie $academie): self
    {
        $this->academie = $academie;

        return $this;
    }

    /**
     * @return Collection<int, Iut>
     */
    public function getIuts(): Collection
    {
        return $this->iuts;
    }

    public function addIut(Iut $iut): self
    {
        if (!$this->iuts->contains($iut)) {
            $this->iuts[] = $iut;
            $iut->setUniversite($this);
        }

        return $this;
    }

    public function removeIut(Iut $iut): self
    {
        // set the owning side to null (unless already changed)
        if ($this->iuts->removeElement($iut) && $iut->getUniversite() === $this) {
            $iut->setUniversite(null);
        }

        return $this;
    }
}
