<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcRessource.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 12/05/2021 15:22
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcRessourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApcRessourceRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ApcRessource extends AbstractMatiere
{
    use LifeCycleTrait;

    public const SOURCE = 'ressource';

    /**
     * @ORM\ManyToOne(targetEntity=Semestre::class, inversedBy="apcRessources")
     */
    private ?Semestre $semestre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $motsCles;

    /**
     * @ORM\OneToMany(targetEntity=ApcRessourceCompetence::class, mappedBy="ressource", cascade={"persist","remove"} )
     */
    private Collection $apcRessourceCompetences;

    /**
     * @ORM\OneToMany(targetEntity=ApcRessourceApprentissageCritique::class, mappedBy="ressource", cascade={"persist","remove"})
     */
    private Collection $apcRessourceApprentissageCritiques;

    /**
     * @ORM\OneToMany(targetEntity=ApcSaeRessource::class, mappedBy="ressource", cascade={"persist","remove"})
     */
    private Collection $apcSaeRessources;

    /**
     * @ORM\OneToMany(targetEntity=ApcRessourceParcours::class, mappedBy="ressource", cascade={"persist","remove"})
     */
    private Collection $apcRessourceParcours;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $ordre = 1;

    /**
     * @ORM\ManyToMany(targetEntity=ApcRessource::class, inversedBy="apcRessources")
     */
    private Collection $ressourcesPreRequises;

    /**
     * @ORM\ManyToMany(targetEntity=ApcRessource::class, mappedBy="ressourcesPreRequises")
     */
    private Collection $apcRessources;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $typeFiche;

    public function __construct()
    {
        $this->apcRessourceCompetences = new ArrayCollection();
        $this->apcRessourceApprentissageCritiques = new ArrayCollection();
        $this->apcSaeRessources = new ArrayCollection();
        $this->apcRessourceParcours = new ArrayCollection();
        $this->ressourcesPreRequises = new ArrayCollection();
        $this->apcRessources = new ArrayCollection();
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

    public function getMotsCles(): ?string
    {
        return $this->motsCles;
    }

    public function setMotsCles(?string $motsCles): self
    {
        $this->motsCles = $motsCles;

        return $this;
    }

    /**
     * @return Collection|ApcRessourceCompetence[]
     */
    public function getApcRessourceCompetences(): Collection
    {
        return $this->apcRessourceCompetences;
    }

    public function addApcRessourceCompetence(ApcRessourceCompetence $apcRessourceCompetence): self
    {
        if (!$this->apcRessourceCompetences->contains($apcRessourceCompetence)) {
            $this->apcRessourceCompetences[] = $apcRessourceCompetence;
            $apcRessourceCompetence->setRessource($this);
        }

        return $this;
    }

    public function removeApcRessourceCompetence(ApcRessourceCompetence $apcRessourceCompetence): self
    {
        if ($this->apcRessourceCompetences->removeElement($apcRessourceCompetence)) {
            // set the owning side to null (unless already changed)
            if ($apcRessourceCompetence->getRessource() === $this) {
                $apcRessourceCompetence->setRessource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ApcRessourceApprentissageCritique[]
     */
    public function getApcRessourceApprentissageCritiques(): Collection
    {
        return $this->apcRessourceApprentissageCritiques;
    }

    public function addApcRessourceApprentissageCritique(ApcRessourceApprentissageCritique $apcRessourceApprentissageCritique): self
    {
        if (!$this->apcRessourceApprentissageCritiques->contains($apcRessourceApprentissageCritique)) {
            $this->apcRessourceApprentissageCritiques[] = $apcRessourceApprentissageCritique;
            $apcRessourceApprentissageCritique->setRessource($this);
        }

        return $this;
    }

    public function removeApcRessourceApprentissageCritique(ApcRessourceApprentissageCritique $apcRessourceApprentissageCritique): self
    {
        if ($this->apcRessourceApprentissageCritiques->removeElement($apcRessourceApprentissageCritique)) {
            // set the owning side to null (unless already changed)
            if ($apcRessourceApprentissageCritique->getRessource() === $this) {
                $apcRessourceApprentissageCritique->setRessource(null);
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
            $apcSaeRessource->setRessource($this);
        }

        return $this;
    }

    public function removeApcSaeRessource(ApcSaeRessource $apcSaeRessource): self
    {
        if ($this->apcSaeRessources->removeElement($apcSaeRessource)) {
            // set the owning side to null (unless already changed)
            if ($apcSaeRessource->getRessource() === $this) {
                $apcSaeRessource->setRessource(null);
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

        foreach ($this->getApcRessourceCompetences() as $apcRessourceCompetence) {
            $comptences->add($apcRessourceCompetence->getCompetence());
        }

        return $comptences;
    }

    public function addCompetence(ApcCompetence $competence): self
    {
        $apcRessourceCompetence = new ApcRessourceCompetence($this, $competence);
        $this->addApcRessourceCompetence($apcRessourceCompetence);

        return $this;
    }

    public function removeCompetence(ApcCompetence $competence): self
    {
        foreach ($this->apcRessourceCompetences as $apcRessourceCompetence) {
            if ($apcRessourceCompetence->getCompetence() === $competence) {
                $this->apcRessourceCompetences->removeElement($apcRessourceCompetence);
            }
        }

        return $this;
    }

    public function getNiveau(): ?int
    {
        if (count($this->apcRessourceApprentissageCritiques) > 0) {
            $ac = $this->apcRessourceApprentissageCritiques[0]->getApprentissageCritique();

            return null !== $ac ? $ac->getNiveau()->getOrdre() : 0;
        }

        return null;
    }

    /**
     * @return Collection|ApcRessourceParcours[]
     */
    public function getApcRessourceParcours(): Collection
    {
        return $this->apcRessourceParcours;
    }

    public function addApcRessourceParcour(ApcRessourceParcours $apcRessourceParcour): self
    {
        if (!$this->apcRessourceParcours->contains($apcRessourceParcour)) {
            $this->apcRessourceParcours[] = $apcRessourceParcour;
            $apcRessourceParcour->setRessource($this);
        }

        return $this;
    }

    public function removeApcRessourceParcour(ApcRessourceParcours $apcRessourceParcour): self
    {
        if ($this->apcRessourceParcours->removeElement($apcRessourceParcour)) {
            // set the owning side to null (unless already changed)
            if ($apcRessourceParcour->getRessource() === $this) {
                $apcRessourceParcour->setRessource(null);
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

    /**
     * @return Collection|self[]
     */
    public function getRessourcesPreRequises(): Collection
    {
        return $this->ressourcesPreRequises;
    }

    public function addRessourcesPreRequise(self $ressourcesPreRequise): self
    {
        if (!$this->ressourcesPreRequises->contains($ressourcesPreRequise)) {
            $this->ressourcesPreRequises[] = $ressourcesPreRequise;
        }

        return $this;
    }

    public function removeRessourcesPreRequise(self $ressourcesPreRequise): self
    {
        $this->ressourcesPreRequises->removeElement($ressourcesPreRequise);

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getApcRessources(): Collection
    {
        return $this->apcRessources;
    }

    public function addApcRessource(self $apcRessource): self
    {
        if (!$this->apcRessources->contains($apcRessource)) {
            $this->apcRessources[] = $apcRessource;
            $apcRessource->addRessourcesPreRequise($this);
        }

        return $this;
    }

    public function removeApcRessource(self $apcRessource): self
    {
        if ($this->apcRessources->removeElement($apcRessource)) {
            $apcRessource->removeRessourcesPreRequise($this);
        }

        return $this;
    }

    public function getDepartement():?Departement
    {
        return $this->getSemestre()?->getAnnee()?->getDepartement();
    }

    public function getTypeFiche(): ?string
    {
        return $this->typeFiche;
    }

    public function setTypeFiche(?string $typeFiche): self
    {
        $this->typeFiche = $typeFiche;

        return $this;
    }
}
