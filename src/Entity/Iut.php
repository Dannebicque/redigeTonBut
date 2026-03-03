<?php

namespace App\Entity;

use App\Repository\IutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IutRepository::class)]
class Iut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(targetEntity: IutUniversite::class, inversedBy: 'iuts')]
    private ?IutUniversite $universite = null;

    /**
     * @var Collection<int, IutSite>
     */
    #[ORM\OneToMany(targetEntity: IutSite::class, mappedBy: 'iut')]
    private Collection $iutSites;

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
        // set the owning side to null (unless already changed)
        if ($this->iutSites->removeElement($iutSite) && $iutSite->getIut() === $this) {
            $iutSite->setIut(null);
        }

        return $this;
    }
}
