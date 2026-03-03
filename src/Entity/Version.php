<?php

namespace App\Entity;

use App\Repository\VersionRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VersionRepository::class)]
class Version
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    private ?Departement $departement = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 50)]
    private ?string $etatPublication = null;

    /**
     * @var Collection<int, Annee>
     */
    #[ORM\OneToMany(mappedBy: 'version', targetEntity: Annee::class)]
    private Collection $annees;

    /**
     * @var Collection<int, ApcCompetence>
     */
    #[ORM\OneToMany(mappedBy: 'version', targetEntity: ApcCompetence::class)]
    private Collection $apcCompetences;

    /**
     * @var Collection<int, ApcParcours>
     */
    #[ORM\OneToMany(mappedBy: 'version', targetEntity: ApcParcours::class)]
    private Collection $apcParcours;

    /**
     * @var Collection<int, QapesSae>
     */
    #[ORM\OneToMany(mappedBy: 'versionDepartement', targetEntity: QapesSae::class)]
    private Collection $qapesSaes;

    #[ORM\Column]
    private ?int $annee = null;

    #[ORM\Column]
    private ?bool $actif = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $textePresentation = null;

    #[ORM\Column]
    private ?bool $verouilleStructure = true;

    #[ORM\Column]
    private ?bool $verouilleCompetences = true;

    #[ORM\Column]
    private ?bool $verouilleCroise = true;

    #[ORM\Column]
    private ?\DateTime $dateVersionCompetence = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateVersionFormation = null;

    #[ORM\Column]
    private ?bool $pnVerouille = true;

    #[ORM\Column]
    private ?bool $coeffVerouille = true;

    #[ORM\Column]
    private ?float $altBut1 = null;

    #[ORM\Column]
    private ?float $altBut2 = null;

    #[ORM\Column]
    private ?float $altBut3 = null;

    #[ORM\Column]
    private ?bool $textesVerouilles = true;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $previousVersion = null;

    public function __construct(Departement $departement)
    {
        $this->departement = $departement;
        $this->annees = new ArrayCollection();
        $this->apcCompetences = new ArrayCollection();
        $this->apcParcours = new ArrayCollection();
        $this->qapesSaes = new ArrayCollection();
        $this->dateVersionCompetence = new \DateTime();
    }

    public function __toString(): string
    {
        return $this->getDepartement()->getSigle().' - '. $this->getLibelle();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getEtatPublication(): ?string
    {
        return $this->etatPublication;
    }

    public function setEtatPublication(string $etatPublication): static
    {
        $this->etatPublication = $etatPublication;

        return $this;
    }

    /**
     * @return Collection<int, Annee>
     */
    public function getAnnees(): Collection
    {
        return $this->annees;
    }

    public function addAnnee(Annee $annee): static
    {
        if (!$this->annees->contains($annee)) {
            $this->annees->add($annee);
            $annee->setVersion($this);
        }

        return $this;
    }

    public function removeAnnee(Annee $annee): static
    {
        if ($this->annees->removeElement($annee)) {
            // set the owning side to null (unless already changed)
            if ($annee->getVersion() === $this) {
                $annee->setVersion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApcCompetence>
     */
    public function getApcCompetences(): Collection
    {
        return $this->apcCompetences;
    }

    public function addApcCompetence(ApcCompetence $apcCompetence): static
    {
        if (!$this->apcCompetences->contains($apcCompetence)) {
            $this->apcCompetences->add($apcCompetence);
            $apcCompetence->setVersion($this);
        }

        return $this;
    }

    public function removeApcCompetence(ApcCompetence $apcCompetence): static
    {
        if ($this->apcCompetences->removeElement($apcCompetence)) {
            // set the owning side to null (unless already changed)
            if ($apcCompetence->getVersion() === $this) {
                $apcCompetence->setVersion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApcParcours>
     */
    public function getApcParcours(): Collection
    {
        return $this->apcParcours;
    }

    public function addApcParcour(ApcParcours $apcParcour): static
    {
        if (!$this->apcParcours->contains($apcParcour)) {
            $this->apcParcours->add($apcParcour);
            $apcParcour->setVersion($this);
        }

        return $this;
    }

    public function removeApcParcour(ApcParcours $apcParcour): static
    {
        if ($this->apcParcours->removeElement($apcParcour)) {
            // set the owning side to null (unless already changed)
            if ($apcParcour->getVersion() === $this) {
                $apcParcour->setVersion(null);
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

    public function addQapesSae(QapesSae $qapesSae): static
    {
        if (!$this->qapesSaes->contains($qapesSae)) {
            $this->qapesSaes->add($qapesSae);
            $qapesSae->setVersionDepartement($this);
        }

        return $this;
    }

    public function removeQapesSae(QapesSae $qapesSae): static
    {
        if ($this->qapesSaes->removeElement($qapesSae)) {
            // set the owning side to null (unless already changed)
            if ($qapesSae->getVersionDepartement() === $this) {
                $qapesSae->setVersionDepartement(null);
            }
        }

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getTextePresentation(): ?string
    {
        return $this->textePresentation;
    }

    public function setTextePresentation(?string $textePresentation): static
    {
        $this->textePresentation = $textePresentation;

        return $this;
    }

    public function isVerouilleStructure(): ?bool
    {
        return $this->verouilleStructure;
    }

    public function setVerouilleStructure(bool $verouilleStructure): static
    {
        $this->verouilleStructure = $verouilleStructure;

        return $this;
    }

    public function isVerouilleCompetences(): ?bool
    {
        return $this->verouilleCompetences;
    }

    public function setVerouilleCompetences(bool $verouilleCompetences): static
    {
        $this->verouilleCompetences = $verouilleCompetences;

        return $this;
    }

    public function isVerouilleCroise(): ?bool
    {
        return $this->verouilleCroise;
    }

    public function setVerouilleCroise(bool $verouilleCroise): static
    {
        $this->verouilleCroise = $verouilleCroise;

        return $this;
    }

    public function getDateVersionCompetence(): ?\DateTime
    {
        return $this->dateVersionCompetence;
    }

    public function setDateVersionCompetence(?\DateTimeInterface $dateVersionCompetence): static
    {
        $this->dateVersionCompetence = $dateVersionCompetence;

        return $this;
    }

    public function getDateVersionFormation(): ?\DateTimeInterface
    {
        return $this->dateVersionFormation;
    }

    public function setDateVersionFormation(?\DateTime $dateVersionFormation): static
    {
        $this->dateVersionFormation = $dateVersionFormation;

        return $this;
    }

    public function isPnVerouille(): ?bool
    {
        return $this->pnVerouille;
    }

    public function setPnVerouille(bool $pnVerouille): static
    {
        $this->pnVerouille = $pnVerouille;

        return $this;
    }

    public function isCoeffVerouille(): ?bool
    {
        return $this->coeffVerouille;
    }

    public function setCoeffVerouille(bool $coeffVerouille): static
    {
        $this->coeffVerouille = $coeffVerouille;

        return $this;
    }

    public function getAltBut1(): ?float
    {
        return $this->altBut1;
    }

    public function setAltBut1(float $altBut1): static
    {
        $this->altBut1 = $altBut1;

        return $this;
    }

    public function getAltBut2(): ?float
    {
        return $this->altBut2;
    }

    public function setAltBut2(float $altBut2): static
    {
        $this->altBut2 = $altBut2;

        return $this;
    }

    public function getAltBut3(): ?float
    {
        return $this->altBut3;
    }

    public function setAltBut3(float $altBut3): static
    {
        $this->altBut3 = $altBut3;

        return $this;
    }

    public function isTextesVerouilles(): ?bool
    {
        return $this->textesVerouilles;
    }

    public function setTextesVerouilles(bool $textesVerouilles): static
    {
        $this->textesVerouilles = $textesVerouilles;

        return $this;
    }

    public function getSemestres(): array
    {
        $semestres = [];
        foreach ($this->getAnnees() as $annee) {
            foreach ($annee->getSemestres() as $semestre) {
                $semestres[] = $semestre;
            }
        }

        return $semestres;
    }

    public function getPreviousVersion(): ?self
    {
        return $this->previousVersion;
    }

    public function setPreviousVersion(?self $previousVersion): static
    {
        $this->previousVersion = $previousVersion;

        return $this;
    }
}
