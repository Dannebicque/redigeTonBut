<?php

namespace App\Entity;

use App\Repository\IutVilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IutVilleRepository::class)]
class IutVille
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(targetEntity: IutRegion::class, inversedBy: 'iutVilles')]
    private ?\App\Entity\IutRegion $region = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\IutSite>
     */
    #[ORM\OneToMany(targetEntity: IutSite::class, mappedBy: 'ville')]
    private \Doctrine\Common\Collections\Collection $iutSites;

    public function __construct()
    {
        $this->iutSites = new ArrayCollection();
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
     * @return Collection<int, IutSite>
     */
    public function getIutSites(): Collection
    {
        return $this->iutSites;
    }

    public function addIutSite(IutSite $iutSite): self
    {
        if (!$this->iutSites->contains($iutSite)) {
            $this->iutSites[] = $iutSite;
            $iutSite->setVille($this);
        }

        return $this;
    }

    public function removeIutSite(IutSite $iutSite): self
    {
        // set the owning side to null (unless already changed)
        if ($this->iutSites->removeElement($iutSite) && $iutSite->getVille() === $this) {
            $iutSite->setVille(null);
        }

        return $this;
    }
}
