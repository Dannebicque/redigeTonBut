<?php

namespace App\Entity;

use App\Repository\IutSiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IutSiteRepository::class)]
class IutSite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(targetEntity: Iut::class, inversedBy: 'iutSites')]
    private ?Iut $iut = null;

    #[ORM\ManyToOne(targetEntity: IutVille::class, inversedBy: 'iutSites')]
    private ?IutVille $ville = null;

    /**
     * @var Collection<int, IutSiteParcours>
     */
    #[ORM\OneToMany(targetEntity: IutSiteParcours::class, mappedBy: 'site')]
    private Collection $iutSiteParcours;

    /**
     * @var Collection<int, QapesSae>
     */
    #[ORM\OneToMany(targetEntity: QapesSae::class, mappedBy: 'iutSite')]
    private Collection $qapesSaes;

    public function __construct()
    {
        $this->iutSiteParcours = new ArrayCollection();
        $this->qapesSaes = new ArrayCollection();
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

    public function getIut(): ?Iut
    {
        return $this->iut;
    }

    public function setIut(?Iut $iut): self
    {
        $this->iut = $iut;

        return $this;
    }

    public function getVille(): ?IutVille
    {
        return $this->ville;
    }

    public function setVille(?IutVille $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection<int, IutSiteParcours>
     */
    public function getIutSiteParcours(): Collection
    {
        return $this->iutSiteParcours;
    }

    public function addIutSiteParcour(IutSiteParcours $iutSiteParcour): self
    {
        if (!$this->iutSiteParcours->contains($iutSiteParcour)) {
            $this->iutSiteParcours[] = $iutSiteParcour;
            $iutSiteParcour->setSite($this);
        }

        return $this;
    }

    public function removeIutSiteParcour(IutSiteParcours $iutSiteParcour): self
    {
        // set the owning side to null (unless already changed)
        if ($this->iutSiteParcours->removeElement($iutSiteParcour) && $iutSiteParcour->getSite() === $this) {
            $iutSiteParcour->setSite(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, QapesSae>
     */
    public function getQapesSaes(): Collection
    {
        return $this->qapesSaes;
    }

    public function addQapesSae(QapesSae $qapesSae): self
    {
        if (!$this->qapesSaes->contains($qapesSae)) {
            $this->qapesSaes[] = $qapesSae;
            $qapesSae->setIutSite($this);
        }

        return $this;
    }

    public function removeQapesSae(QapesSae $qapesSae): self
    {
        // set the owning side to null (unless already changed)
        if ($this->qapesSaes->removeElement($qapesSae) && $qapesSae->getIutSite() === $this) {
            $qapesSae->setIutSite(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->libelle . ' - ' . $this->iut->getLibelle() . ' - ' . $this->ville->getLibelle();
    }
}
