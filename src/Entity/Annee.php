<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/Annee.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 01/06/2021 08:08
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnneeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Annee extends BaseEntity
{
    use LifeCycleTrait;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private ?string $codeEtape;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"annee"})
     */
    private ?string $libelle;

    /**
     * @ORM\Column(name="ordre", type="integer")
     */
    private int $ordre = 1;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"annee"})
     */
    private ?string $libelleLong;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Semestre", mappedBy="annee")
     * @ORM\OrderBy({"ordreLmd"="ASC"})
     */
    private Collection $semestres;

    /**
     * @ORM\OneToMany(targetEntity=ApcNiveau::class, mappedBy="annee")
     */
    private Collection $apcNiveaux;

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class, inversedBy="annees")
     */
    private $departement;

    public function __construct()
    {
        $this->semestres = new ArrayCollection();
        $this->apcNiveaux = new ArrayCollection();
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle($libelle): void
    {
        $this->libelle = $libelle;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): void
    {
        $this->ordre = $ordre;
    }

    public function getLibelleLong(): ?string
    {
        return $this->libelleLong;
    }

    public function setLibelleLong(string $libelleLong): void
    {
        $this->libelleLong = $libelleLong;
    }


    /**
     * @return Collection|Semestre[]
     */
    public function getSemestres(): Collection
    {
        return $this->semestres;
    }

    public function addSemestre(Semestre $semestre): self
    {
        if (!$this->semestres->contains($semestre)) {
            $this->semestres[] = $semestre;
            $semestre->setAnnee($this);
        }

        return $this;
    }

    public function removeSemestre(Semestre $semestre): self
    {
        if ($this->semestres->contains($semestre)) {
            $this->semestres->removeElement($semestre);
            // set the owning side to null (unless already changed)
            if ($semestre->getAnnee() === $this) {
                $semestre->setAnnee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ApcNiveau[]
     */
    public function getApcNiveaux(): Collection
    {
        return $this->apcNiveaux;
    }

    public function addApcNiveau(ApcNiveau $apcNiveau): self
    {
        if (!$this->apcNiveaux->contains($apcNiveau)) {
            $this->apcNiveaux[] = $apcNiveau;
            $apcNiveau->setAnnee($this);
        }

        return $this;
    }

    public function removeApcNiveau(ApcNiveau $apcNiveau): self
    {
        if ($this->apcNiveaux->contains($apcNiveau)) {
            $this->apcNiveaux->removeElement($apcNiveau);
            // set the owning side to null (unless already changed)
            if ($apcNiveau->getAnnee() === $this) {
                $apcNiveau->setAnnee(null);
            }
        }

        return $this;
    }

    public function getCodeEtape(): ?string
    {
        return $this->codeEtape;
    }

    public function setCodeEtape($codeEtape): void
    {
        $this->codeEtape = $codeEtape;
    }

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): self
    {
        $this->departement = $departement;

        return $this;
    }
}
