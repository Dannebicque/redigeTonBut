<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/Semestre.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 09/05/2021 14:41
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SemestreRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Semestre extends BaseEntity
{
    use LifeCycleTrait;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"article_administration", "date_administration", "semestre",
     *                                    "etudiants_administration","document_administration"})
     */
    private ?string $libelle;

    /**
     * @ORM\Column(type="integer")
     */
    private int $ordreAnnee; //dans l'année

    /**
     * @ORM\Column(type="integer")
     */
    private int $ordreLmd; //dans le LMD


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Semestre")
     */
    private ?Semestre $precedent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Semestre")
     */
    private ?Semestre $suivant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Annee", inversedBy="semestres")
     * @Groups({"semestre"})
     */
    private ?Annee $annee;

    /**
     * @ORM\OneToMany(targetEntity=ApcRessource::class, mappedBy="semestre")
     * @ORM\OrderBy({"ordre":"ASC"})
     */
    private Collection $apcRessources;

    /**
     * @ORM\OneToMany(targetEntity=ApcSae::class, mappedBy="semestre")
     */
    private Collection $apcSaes;

    /**
     * @ORM\Column(type="float")
     */
    private float $nbHeuresRessourceSae = 0;

    /**
     * @ORM\Column(type="float")
     */
    private float $pourcentageAdaptationLocale = 0;

    /**
     * @ORM\Column(type="float")
     */
    private float $nbHeuresSae = 0;

    /**
     * @ORM\Column(type="float")
     */
    private float $nbHeuresRessources = 0;

    /**
     * @ORM\Column(type="float")
     */
    private float $nbHeuresProjet = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $nbSemaineStageMin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $nbSemainesStageMax = 0;

    /**
     * @ORM\OneToMany(targetEntity=ApcCompetenceSemestre::class, mappedBy="semestre")
     */
    private Collection $apcCompetenceSemestres;

    /**
     * @ORM\Column(type="float")
     */
    private float $nbHeuresEnseignementSaeLocale = 0;

    /**
     * @ORM\Column(type="float")
     */
    private float $nbHeuresEnseignementLocale = 0;

    /**
     * @ORM\Column(type="float")
     */
    private float $nbHeuresEnseignementRessourceLocale = 0;

    public function __construct()
    {
        $this->apcRessources = new ArrayCollection();
        $this->apcSaes = new ArrayCollection();
        $this->apcCompetenceSemestres = new ArrayCollection();
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle($libelle): void
    {
        $this->libelle = $libelle;
    }

    public function getOrdreAnnee(): ?int
    {
        return $this->ordreAnnee;
    }

    public function setOrdreAnnee(int $ordreAnnee = 1): void
    {
        $this->ordreAnnee = $ordreAnnee;
    }

    public function getOrdreLmd(): ?int
    {
        return $this->ordreLmd;
    }

    public function setOrdreLmd(int $ordreLmd): void
    {
        $this->ordreLmd = $ordreLmd;
    }


    public function getPrecedent(): ?self
    {
        return $this->precedent;
    }

    public function setPrecedent(?self $precedent): void
    {
        $this->precedent = $precedent;
    }

    public function getSuivant(): ?self
    {
        return $this->suivant;
    }

    public function setSuivant(?self $suivant): void
    {
        $this->suivant = $suivant;
    }

    public function display(): string
    {
        if (null !== $this->getAnnee()) {
            return $this->libelle.' | '.$this->getAnnee()->getLibelle();
        }

        return $this->libelle;
    }

    public function getAnnee(): ?Annee
    {
        return $this->annee;
    }

    public function setAnnee(Annee $annee): void
    {
        $this->annee = $annee;
    }

    /**
     * @return Collection|ApcRessource[]
     */
    public function getApcRessources(): Collection
    {
        return $this->apcRessources;
    }

    public function addApcRessource(ApcRessource $apcRessource): self
    {
        if (!$this->apcRessources->contains($apcRessource)) {
            $this->apcRessources[] = $apcRessource;
            $apcRessource->setSemestre($this);
        }

        return $this;
    }

    public function removeApcRessource(ApcRessource $apcRessource): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcRessources->removeElement($apcRessource) && $apcRessource->getSemestre() === $this) {
            $apcRessource->setSemestre(null);
        }

        return $this;
    }

    /**
     * @return Collection|ApcSae[]
     */
    public function getApcSaes(): Collection
    {
        return $this->apcSaes;
    }

    public function addApcSae(ApcSae $apcSae): self
    {
        if (!$this->apcSaes->contains($apcSae)) {
            $this->apcSaes[] = $apcSae;
            $apcSae->setSemestre($this);
        }

        return $this;
    }

    public function removeApcSae(ApcSae $apcSae): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcSaes->removeElement($apcSae) && $apcSae->getSemestre() === $this) {
            $apcSae->setSemestre(null);
        }

        return $this;
    }

    public function getNbHeuresRessourceSae(): float
    {
        return $this->nbHeuresRessourceSae;
    }

    public function setNbHeuresRessourceSae(float $nbHeuresRessourceSae): self
    {
        $this->nbHeuresRessourceSae = $nbHeuresRessourceSae;

        return $this;
    }

    public function getPourcentageAdaptationLocale(): float
    {
        return $this->pourcentageAdaptationLocale;
    }

    public function setPourcentageAdaptationLocale(float $pourcentageAdaptationLocale): self
    {
        $this->pourcentageAdaptationLocale = $pourcentageAdaptationLocale;

        return $this;
    }

    public function getNbHeuresSae(): float
    {
        return $this->nbHeuresSae;
    }

    public function setNbHeuresSae(float $nbHeuresSae): self
    {
        $this->nbHeuresSae = $nbHeuresSae;

        return $this;
    }

    public function getNbHeuresRessources(): float
    {
        return $this->nbHeuresRessources;
    }

    public function setNbHeuresRessources(float $nbHeuresRessources): self
    {
        $this->nbHeuresRessources = $nbHeuresRessources;

        return $this;
    }

    public function getNbHeuresProjet(): float
    {
        return $this->nbHeuresProjet;
    }

    public function setNbHeuresProjet(float $nbHeuresProjet): self
    {
        $this->nbHeuresProjet = $nbHeuresProjet;

        return $this;
    }

    public function getNbSemaineStageMin(): int
    {
        return $this->nbSemaineStageMin;
    }

    public function setNbSemaineStageMin(int $nbSemaineStageMin): self
    {
        $this->nbSemaineStageMin = $nbSemaineStageMin;

        return $this;
    }

    public function getNbSemainesStageMax(): int
    {
        return $this->nbSemainesStageMax;
    }

    public function setNbSemainesStageMax(int $nbSemainesStageMax): self
    {
        $this->nbSemainesStageMax = $nbSemainesStageMax;

        return $this;
    }

    /**
     * @return Collection|ApcCompetenceSemestre[]
     */
    public function getApcCompetenceSemestres(): Collection
    {
        return $this->apcCompetenceSemestres;
    }

    public function addApcCompetenceSemestre(ApcCompetenceSemestre $apcCompetenceSemestre): self
    {
        if (!$this->apcCompetenceSemestres->contains($apcCompetenceSemestre)) {
            $this->apcCompetenceSemestres[] = $apcCompetenceSemestre;
            $apcCompetenceSemestre->setSemestre($this);
        }

        return $this;
    }

    public function removeApcCompetenceSemestre(ApcCompetenceSemestre $apcCompetenceSemestre): self
    {
        if ($this->apcCompetenceSemestres->removeElement($apcCompetenceSemestre)) {
            // set the owning side to null (unless already changed)
            if ($apcCompetenceSemestre->getSemestre() === $this) {
                $apcCompetenceSemestre->setSemestre(null);
            }
        }

        return $this;
    }

    public function getDepartement(): ?Departement
    {
        return $this->getAnnee()?->getDepartement();
    }

    public function getNbHeuresEnseignementSaeLocale(): float
    {
        return $this->nbHeuresEnseignementSaeLocale;
    }

    public function setNbHeuresEnseignementSaeLocale(float $nbHeuresEnseignementSaeLocale): self
    {
        $this->nbHeuresEnseignementSaeLocale = $nbHeuresEnseignementSaeLocale;

        return $this;
    }

    public function getNbHeuresEnseignementLocale(): float
    {
        return $this->nbHeuresEnseignementLocale;
    }

    public function setNbHeuresEnseignementLocale(float $nbHeuresEnseignementLocale): self
    {
        $this->nbHeuresEnseignementLocale = $nbHeuresEnseignementLocale;

        return $this;
    }

    public function getNbHeuresEnseignementRessourceLocale(): float
    {
        return $this->nbHeuresEnseignementRessourceLocale;
    }

    public function setNbHeuresEnseignementRessourceLocale(float $nbHeuresEnseignementRessourceLocale): self
    {
        $this->nbHeuresEnseignementRessourceLocale = $nbHeuresEnseignementRessourceLocale;

        return $this;
    }
}
