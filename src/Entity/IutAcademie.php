<?php

namespace App\Entity;

use App\Repository\IutAcademieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IutAcademieRepository::class)
 */
class IutAcademie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $libelle;

    /**
     * @ORM\ManyToOne(targetEntity=IutRegion::class, inversedBy="iutAcademies")
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity=IutUniversite::class, mappedBy="academie")
     */
    private $iutUniversites;

    public function __construct()
    {
        $this->iutUniversites = new ArrayCollection();
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

    public function getRegion(): ?IutRegion
    {
        return $this->region;
    }

    public function setRegion(?IutRegion $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection<int, IutUniversite>
     */
    public function getIutUniversites(): Collection
    {
        return $this->iutUniversites;
    }

    public function addIutUniversite(IutUniversite $iutUniversite): self
    {
        if (!$this->iutUniversites->contains($iutUniversite)) {
            $this->iutUniversites[] = $iutUniversite;
            $iutUniversite->setAcademie($this);
        }

        return $this;
    }

    public function removeIutUniversite(IutUniversite $iutUniversite): self
    {
        if ($this->iutUniversites->removeElement($iutUniversite)) {
            // set the owning side to null (unless already changed)
            if ($iutUniversite->getAcademie() === $this) {
                $iutUniversite->setAcademie(null);
            }
        }

        return $this;
    }
}
