<?php

namespace App\Entity;

use App\Repository\IutSiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IutSiteRepository::class)
 */
class IutSite
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
     * @ORM\ManyToOne(targetEntity=Iut::class, inversedBy="iutSites")
     */
    private $iut;

    /**
     * @ORM\ManyToOne(targetEntity=IutVille::class, inversedBy="iutSites")
     */
    private $ville;

    /**
     * @ORM\OneToMany(targetEntity=IutSiteParcours::class, mappedBy="site")
     */
    private $iutSiteParcours;

    /**
     * @ORM\OneToMany(targetEntity=QapesSae::class, mappedBy="iutSite")
     */
    private $qapesSaes;

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
        if ($this->iutSiteParcours->removeElement($iutSiteParcour)) {
            // set the owning side to null (unless already changed)
            if ($iutSiteParcour->getSite() === $this) {
                $iutSiteParcour->setSite(null);
            }
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
        if ($this->qapesSaes->removeElement($qapesSae)) {
            // set the owning side to null (unless already changed)
            if ($qapesSae->getIutSite() === $this) {
                $qapesSae->setIutSite(null);
            }
        }

        return $this;
    }
}
