<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcSae.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 12/05/2021 15:23
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcSaeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @ORM\Entity(repositoryClass=ApcSaeRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ApcSae extends AbstractMatiere
{
    use LifeCycleTrait;

    public const SOURCE = 'sae';

    /**
     * @ORM\ManyToOne(targetEntity=Semestre::class, inversedBy="apcSaes")
     */
    private ?Semestre $semestre;

    /**
     * @ORM\Column(type="float")
     */
    private float $projetPpn = 0;

    /**
     * @ORM\OneToMany(targetEntity=ApcSaeCompetence::class, mappedBy="sae", cascade={"persist","remove"})
     */
    private Collection $apcSaeCompetences;

    /**
     * @ORM\OneToMany(targetEntity=ApcSaeRessource::class, mappedBy="sae", cascade={"persist","remove"})
     */
    private Collection $apcSaeRessources;

    /**
     * @ORM\OneToMany(targetEntity=ApcSaeApprentissageCritique::class, mappedBy="sae", cascade={"persist","remove"})
     */
    private Collection $apcSaeApprentissageCritiques;

    /**
     * @ORM\OneToMany(targetEntity=ApcSaeParcours::class, mappedBy="sae", cascade={"persist","remove"}, fetch="EAGER")
     */
    private Collection $apcSaeParcours;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $ordre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $objectifs;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $exemples;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $ficheAdaptationLocale = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $portfolio = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $stage = false;

    public function __construct()
    {
        $this->apcSaeCompetences = new ArrayCollection();
        $this->apcSaeRessources = new ArrayCollection();
        $this->apcSaeApprentissageCritiques = new ArrayCollection();
        $this->apcSaeParcours = new ArrayCollection();
    }

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

        return $this;
    }

    /**
     * @return Collection|ApcSaeCompetence[]
     */
    public function getApcSaeCompetences(): Collection
    {
        return $this->apcSaeCompetences;
    }

    public function addApcSaeCompetence(ApcSaeCompetence $apcSaeCompetence): self
    {
        if (!$this->apcSaeCompetences->contains($apcSaeCompetence)) {
            $this->apcSaeCompetences[] = $apcSaeCompetence;
            $apcSaeCompetence->setSae($this);
        }

        return $this;
    }

    public function removeApcSaeCompetence(ApcSaeCompetence $apcSaeCompetence): self
    {
        if ($this->apcSaeCompetences->removeElement($apcSaeCompetence)) {
            // set the owning side to null (unless already changed)
            if ($apcSaeCompetence->getSae() === $this) {
                $apcSaeCompetence->setSae(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ApcSaeRessource[]
     */
    public function getApcSaeRessources(): Collection
    {
        return $this->apcSaeRessources;
    }

    public function addApcSaeRessource(ApcSaeRessource $apcSaeRessource): self
    {
        if (!$this->apcSaeRessources->contains($apcSaeRessource)) {
            $this->apcSaeRessources[] = $apcSaeRessource;
            $apcSaeRessource->setSae($this);
        }

        return $this;
    }

    public function removeApcSaeRessource(ApcSaeRessource $apcSaeRessource): self
    {
        if ($this->apcSaeRessources->removeElement($apcSaeRessource)) {
            // set the owning side to null (unless already changed)
            if ($apcSaeRessource->getSae() === $this) {
                $apcSaeRessource->setSae(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ApcCompetence[]
     */
    public function getCompetences(): Collection
    {
        $comptences = new ArrayCollection();

        foreach ($this->getApcSaeCompetences() as $apcSaeCompetence) {
            $comptences->add($apcSaeCompetence->getCompetence());
        }

        return $comptences;
    }

    /**
     * @return $this
     */
    public function addCompetence(ApcCompetence $competence): self
    {
        $apcSaeCompetence = new ApcSaeCompetence($this, $competence);
        $this->addApcSaeCompetence($apcSaeCompetence);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeCompetence(ApcCompetence $competence): self
    {
        foreach ($this->apcSaeCompetences as $apcSaeCompetence) {
            if ($apcSaeCompetence->getCompetence() === $competence) {
                $this->apcSaeCompetences->removeElement($apcSaeCompetence);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ApcSaeApprentissageCritique[]
     */
    public function getApcSaeApprentissageCritiques(): Collection
    {
        return $this->apcSaeApprentissageCritiques;
    }

    public function addApcSaeApprentissageCritique(ApcSaeApprentissageCritique $apcSaeApprentissageCritique): self
    {
        if (!$this->apcSaeApprentissageCritiques->contains($apcSaeApprentissageCritique)) {
            $this->apcSaeApprentissageCritiques[] = $apcSaeApprentissageCritique;
            $apcSaeApprentissageCritique->setSae($this);
        }

        return $this;
    }

    public function removeApcSaeApprentissageCritique(ApcSaeApprentissageCritique $apcSaeApprentissageCritique): self
    {
        if ($this->apcSaeApprentissageCritiques->removeElement($apcSaeApprentissageCritique)) {
            // set the owning side to null (unless already changed)
            if ($apcSaeApprentissageCritique->getSae() === $this) {
                $apcSaeApprentissageCritique->setSae(null);
            }
        }

        return $this;
    }

    public function getProjetPpn(): ?float
    {
        return $this->projetPpn;
    }

    public function setProjetPpn(float $projetPpn): self
    {
        $this->projetPpn = $projetPpn;

        return $this;
    }

    /**
     * @return Collection|ApcSaeParcours[]
     */
    public function getApcSaeParcours(): Collection
    {
        return $this->apcSaeParcours;
    }

    public function addApcSaeParcour(ApcSaeParcours $apcSaeParcour): self
    {
        if (!$this->apcSaeParcours->contains($apcSaeParcour)) {
            $this->apcSaeParcours[] = $apcSaeParcour;
            $apcSaeParcour->setSae($this);
        }

        return $this;
    }

    public function removeApcSaeParcour(ApcSaeParcours $apcSaeParcour): self
    {
        if ($this->apcSaeParcours->removeElement($apcSaeParcour)) {
            // set the owning side to null (unless already changed)
            if ($apcSaeParcour->getSae() === $this) {
                $apcSaeParcour->setSae(null);
            }
        }

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getDepartement():?Departement
    {
        return $this->getSemestre()?->getAnnee()?->getDepartement();
    }

    public function getObjectifs(): ?string
    {
        return $this->objectifs;
    }

    public function setObjectifs(string $objectifs): self
    {
        $this->objectifs = $objectifs;

        return $this;
    }

    public function getExemples(): ?string
    {
        return $this->exemples;
    }

    public function setExemples(?string $exemples): self
    {
        $this->exemples = $exemples;

        return $this;
    }

    public function getFicheAdaptationLocale(): ?bool
    {
        return $this->ficheAdaptationLocale;
    }

    public function setFicheAdaptationLocale(?bool $ficheAdaptationLocale): self
    {
        $this->ficheAdaptationLocale = $ficheAdaptationLocale;

        return $this;
    }

    public function getPortfolio(): ?bool
    {
        return $this->portfolio;
    }

    public function setPortfolio(bool $portfolio): self
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    public function getStage(): ?bool
    {
        return $this->stage;
    }

    public function setStage(bool $stage): self
    {
        $this->stage = $stage;

        return $this;
    }

    public function getSlugName()
    {
        $slugger = new AsciiSlugger();
        return $slugger->slug($this->getCodeMatiere());
    }

    public function isGoodParcours(?ApcParcours $apcParcours = null): bool
    {
        if ($apcParcours === null) {
            return true;
        }
        if ($this->apcSaeParcours->count() === 0) {
            //pas de parcours dans la SAE, donc tous les parcours
            return true;
        }

        foreach ($this->apcSaeParcours as $apcSaeParcour) {
            if ($apcSaeParcour->getParcours()->getId() === $apcParcours->getId()) {
                return true;
            }
        }

        return false;
    }

    public function apcSaeRessourcesOrdre(?ApcParcours $apcParcours = null): Collection | array
    {
        $ressources = $this->apcSaeRessources;
        $t = [];
        foreach ($ressources as $ressource)
        {
            if ($ressource->getRessource()->isGoodParcours($apcParcours)) {

                $t[$ressource->getRessource()->getOrdre()] = $ressource->getRessource();
            }
        }
        ksort($t);
        return $t;
    }

    public function apcSaeApprentissageCritiquesOrdre(?ApcParcours $apcParcours = null): Collection | array
    {
        $acs = $this->apcSaeApprentissageCritiques;
        $t = [];
        foreach ($acs as $ac)
        {
            if ($ac->getApprentissageCritique()->getCompetence()->isGoodParcours($apcParcours)) {
                if (!array_key_exists($ac->getApprentissageCritique()->getCompetence()->getCouleur(), $t)) {
                    $t[$ac->getApprentissageCritique()->getCompetence()->getCouleur()] = [];
                }
                $t[$ac->getApprentissageCritique()->getCompetence()->getCouleur()][$ac->getApprentissageCritique()->getOrdre()] = $ac->getApprentissageCritique();
            }
        }
        ksort($t);
        foreach ($t as $couleur => $acs) {
            ksort($t[$couleur]);
        }
        return $t;
    }
}
