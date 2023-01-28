<?php

namespace App\Entity;

use App\Repository\IutVilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IutVilleRepository::class)
 */
class IutVille
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
     * @ORM\ManyToOne(targetEntity=IutRegion::class, inversedBy="iutVilles")
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity=IutSite::class, mappedBy="ville")
     */
    private $iutSites;

    public function __construct()
    {
        $this->iutSites = new ArrayCollection();
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
        if ($this->iutSites->removeElement($iutSite)) {
            // set the owning side to null (unless already changed)
            if ($iutSite->getVille() === $this) {
                $iutSite->setVille(null);
            }
        }

        return $this;
    }
}
