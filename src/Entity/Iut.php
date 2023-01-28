<?php

namespace App\Entity;

use App\Repository\IutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IutRepository::class)
 */
class Iut
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
     * @ORM\ManyToOne(targetEntity=IutUniversite::class, inversedBy="iuts")
     */
    private $universite;

    /**
     * @ORM\OneToMany(targetEntity=IutSite::class, mappedBy="iut")
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

    public function getUniversite(): ?IutUniversite
    {
        return $this->universite;
    }

    public function setUniversite(?IutUniversite $universite): self
    {
        $this->universite = $universite;

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
            $iutSite->setIut($this);
        }

        return $this;
    }

    public function removeIutSite(IutSite $iutSite): self
    {
        if ($this->iutSites->removeElement($iutSite)) {
            // set the owning side to null (unless already changed)
            if ($iutSite->getIut() === $this) {
                $iutSite->setIut(null);
            }
        }

        return $this;
    }
}
