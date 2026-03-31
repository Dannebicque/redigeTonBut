<?php

namespace App\Entity;

use App\Repository\IutAcademieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IutAcademieRepository::class)]
class IutAcademie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(targetEntity: IutRegion::class, inversedBy: 'iutAcademies')]
    private ?IutRegion $region = null;

    /**
     * @var Collection<int, IutUniversite>
     */
    #[ORM\OneToMany(mappedBy: 'academie', targetEntity: IutUniversite::class)]
    private Collection $iutUniversites;

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
        // set the owning side to null (unless already changed)
        if ($this->iutUniversites->removeElement($iutUniversite) && $iutUniversite->getAcademie() === $this) {
            $iutUniversite->setAcademie(null);
        }

        return $this;
    }
}
