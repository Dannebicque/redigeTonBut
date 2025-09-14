<?php

namespace App\Entity;

use App\Repository\IutRegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IutRegionRepository::class)]
class IutRegion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 100)]
    private ?string $libelle = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\IutAcademie>
     */
    #[ORM\OneToMany(targetEntity: IutAcademie::class, mappedBy: 'region')]
    private \Doctrine\Common\Collections\Collection $iutAcademies;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\IutVille>
     */
    #[ORM\OneToMany(targetEntity: IutVille::class, mappedBy: 'region')]
    private \Doctrine\Common\Collections\Collection $iutVilles;

    public function __construct()
    {
        $this->iutAcademies = new ArrayCollection();
        $this->iutVilles = new ArrayCollection();
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
     * @return Collection<int, IutAcademie>
     */
    public function getIutAcademies(): Collection
    {
        return $this->iutAcademies;
    }

    public function addIutAcademy(IutAcademie $iutAcademy): self
    {
        if (!$this->iutAcademies->contains($iutAcademy)) {
            $this->iutAcademies[] = $iutAcademy;
            $iutAcademy->setRegion($this);
        }

        return $this;
    }

    public function removeIutAcademy(IutAcademie $iutAcademy): self
    {
        // set the owning side to null (unless already changed)
        if ($this->iutAcademies->removeElement($iutAcademy) && $iutAcademy->getRegion() === $this) {
            $iutAcademy->setRegion(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, IutVille>
     */
    public function getIutVilles(): Collection
    {
        return $this->iutVilles;
    }

    public function addIutVille(IutVille $iutVille): self
    {
        if (!$this->iutVilles->contains($iutVille)) {
            $this->iutVilles[] = $iutVille;
            $iutVille->setRegion($this);
        }

        return $this;
    }

    public function removeIutVille(IutVille $iutVille): self
    {
        // set the owning side to null (unless already changed)
        if ($this->iutVilles->removeElement($iutVille) && $iutVille->getRegion() === $this) {
            $iutVille->setRegion(null);
        }

        return $this;
    }
}
