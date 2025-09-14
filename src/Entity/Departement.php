<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/Departement.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 05/06/2021 12:02
 */

namespace App\Entity;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\LifeCycleTrait;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: \App\Repository\DepartementRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(SearchFilter::class, properties: ["sigle" => "exact"])]
#[ApiResource(
    normalizationContext: ["groups" => ["read:departement"]],
    collectionOperations: ["get"],
    itemOperations: ["get"]
)]
class Departement
{
    use LifeCycleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public const TERTIAIRE = 'tertiaire';

    public const SECONDAIRE = 'secondaire';

    public const TYPE1 = 'type1';

    public const TYPE2 = 'type2';

    public const TYPE3 = 'type3';

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(["actualite_administration", "read:departement"])]
    private ?string $libelle;

    /**
     * @var Collection<int, \App\Entity\Annee>
     */
    #[ORM\OneToMany(targetEntity: Annee::class, mappedBy: "departement")]
    private Collection $annees;

    /**
     * @var Collection<int, \App\Entity\ApcCompetence>
     */
    #[ORM\OneToMany(targetEntity: ApcCompetence::class, mappedBy: "departement")]
    #[ORM\OrderBy(["couleur" => "ASC"])]
    #[Groups(["read:departement"])]
    private Collection $apcCompetences;

    /**
     * @var Collection<int, \App\Entity\ApcParcours>
     */
    #[ORM\OneToMany(targetEntity: ApcParcours::class, mappedBy: "departement")]
    #[ORM\OrderBy(["ordre" => "ASC"])]
    #[Groups(["read:departement"])]
    private Collection $apcParcours;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Groups(["read:departement"])]
    private ?string $typeDepartement;

    #[ORM\Column(type: Types::STRING, length: 20, unique: true)]
    #[ApiProperty(identifier: true)]
    #[Groups(["read:departement"])]
    private ?string $sigle;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(["read:departement"])]
    private ?int $numeroAnnexe;

    #[ORM\Column(type: Types::STRING, length: 5)]
    #[Groups(["read:departement"])]
    private ?string $typeStructure;

    /**
     * @var Collection<int, \App\Entity\User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: "departement")]
    private Collection $users;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["read:departement"])]
    private ?string $textePresentation;

    #[ORM\ManyToOne(targetEntity: Departement::class, inversedBy: "departementEnfants")]
    private ?\App\Entity\Departement $departement_parent = null;

    /**
     * @var Collection<int, \App\Entity\Departement>
     */
    #[ORM\OneToMany(targetEntity: Departement::class, mappedBy: "departement_parent")]
    private Collection $departementEnfants;

    /**
     * @var Collection<int, \App\Entity\User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "CpnDepartements")]
    private Collection $cpns;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $verouilleStructure;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $verouilleCompetences;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $verouilleCroise;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["read:departement"])]
    private ?DateTime $dateVersionCompetence;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["read:departement"])]
    private ?DateTime $dateVersionFormation;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $pn_bloque = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $coeff_editable = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(["read:departement"])]
    private ?float $altBut1 = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(["read:departement"])]
    private ?float $altBut2 = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(["read:departement"])]
    private ?float $altBut3 = null;

    /**
     * @var Collection<int, \App\Entity\QapesSae>
     */
    #[ORM\OneToMany(targetEntity: QapesSae::class, mappedBy: "specialite")]
    private Collection $qapesSaes;



    public function __construct()
    {
        $this->annees = new ArrayCollection();
        $this->apcCompetences = new ArrayCollection();
        $this->apcParcours = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->departementEnfants = new ArrayCollection();
        $this->cpns = new ArrayCollection();
        $this->qapesSaes = new ArrayCollection();
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): void
    {
        $this->libelle = $libelle;
    }

    /**
     * @return Collection|Annee[]
     */
    public function getAnnees(): Collection
    {
        return $this->annees;
    }

    public function addAnnee(Annee $annee): self
    {
        if (!$this->annees->contains($annee)) {
            $this->annees[] = $annee;
            $annee->setDepartement($this);
        }

        return $this;
    }

    public function removeAnnee(Annee $annee): self
    {
        // set the owning side to null (unless already changed)
        if ($this->annees->removeElement($annee) && $annee->getDepartement() === $this) {
            $annee->setDepartement(null);
        }

        return $this;
    }

    /**
     * @return Collection|ApcCompetence[]
     */
    public function getApcCompetences(): Collection
    {
        return $this->apcCompetences;
    }

    public function addApcCompetence(ApcCompetence $apcCompetence): self
    {
        if (!$this->apcCompetences->contains($apcCompetence)) {
            $this->apcCompetences[] = $apcCompetence;
            $apcCompetence->setDepartement($this);
        }

        return $this;
    }

    public function removeApcCompetence(ApcCompetence $apcCompetence): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcCompetences->removeElement($apcCompetence) && $apcCompetence->getDepartement() === $this) {
            $apcCompetence->setDepartement(null);
        }

        return $this;
    }

    /**
     * @return Collection|ApcParcours[]
     */
    public function getApcParcours(): Collection
    {
        return $this->apcParcours;
    }

    public function addApcParcour(ApcParcours $apcParcour): self
    {
        if (!$this->apcParcours->contains($apcParcour)) {
            $this->apcParcours[] = $apcParcour;
            $apcParcour->setDepartement($this);
        }

        return $this;
    }

    public function removeApcParcour(ApcParcours $apcParcour): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcParcours->removeElement($apcParcour) && $apcParcour->getDepartement() === $this) {
            $apcParcour->setDepartement(null);
        }

        return $this;
    }

    public function getTypeDepartement(): ?string
    {
        return $this->typeDepartement;
    }

    public function setTypeDepartement(string $typeDepartement): self
    {
        $this->typeDepartement = $typeDepartement;

        return $this;
    }

    public function isTertiaire(): bool
    {
        return $this->typeDepartement === self::TERTIAIRE;
    }

    public function isSecondaire(): bool
    {
        return $this->typeDepartement === self::SECONDAIRE;
    }

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(string $sigle): self
    {
        $this->sigle = $sigle;

        return $this;
    }

    public function getNumeroAnnexe(): ?int
    {
        return $this->numeroAnnexe;
    }

    public function setNumeroAnnexe(int $numeroAnnexe): self
    {
        $this->numeroAnnexe = $numeroAnnexe;

        return $this;
    }

    public function getTypeStructure(): ?string
    {
        return $this->typeStructure;
    }

    public function setTypeStructure(string $typeStructure): self
    {
        $this->typeStructure = $typeStructure;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setDepartement($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        // set the owning side to null (unless already changed)
        if ($this->users->removeElement($user) && $user->getDepartement() === $this) {
            $user->setDepartement(null);
        }

        return $this;
    }

    public function display(): string
    {
        return $this->getSigle().' | '.$this->getLibelle();
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

    public function getTextePresentation(): ?string
    {
        return $this->textePresentation;
    }

    public function setTextePresentation(string $textePresentation): self
    {
        $this->textePresentation = $textePresentation;

        return $this;
    }

    public function getPacd(): ?User
    {
        foreach ($this->getUsers() as $user) {
            if (in_array('ROLE_PACD', $user->getRoles(), true)) {
                return $user;
            }
        }

        return null;
    }

    public function getCpn(): ?User
    {
        foreach ($this->getUsers() as $user) {
            if (in_array('ROLE_CPN', $user->getRoles(), true) || in_array('ROLE_CPN_LECTEUR', $user->getRoles(), true)) {
                return $user;
            }
        }

        return null;
    }

    public function getNbHeuresDiplome(): int
    {
        return $this->isSecondaire() ? 2000 : 1800;
    }

    public function getDepartementParent(): ?self
    {
        return $this->departement_parent;
    }

    public function setDepartementParent(?self $departement_parent): self
    {
        $this->departement_parent = $departement_parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getDepartementEnfants(): Collection
    {
        return $this->departementEnfants;
    }

    public function addDepartementEnfant(self $departementsEnfnat): self
    {
        if (!$this->departementEnfants->contains($departementsEnfnat)) {
            $this->departementEnfants[] = $departementsEnfnat;
            $departementsEnfnat->setDepartementParent($this);
        }

        return $this;
    }

    public function removeDepartementEnfant(self $departementsEnfnat): self
    {
        // set the owning side to null (unless already changed)
        if ($this->departementEnfants->removeElement($departementsEnfnat) && $departementsEnfnat->getDepartementParent() === $this) {
            $departementsEnfnat->setDepartementParent(null);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getCpns(): Collection
    {
        return $this->cpns;
    }

    public function addCpn(User $cpn): self
    {
        if (!$this->cpns->contains($cpn)) {
            $this->cpns[] = $cpn;
            $cpn->addCpnDepartement($this);
        }

        return $this;
    }

    public function removeCpn(User $cpn): self
    {
        if ($this->cpns->removeElement($cpn)) {
            $cpn->removeCpnDepartement($this);
        }

        return $this;
    }

    public function getVerouilleStructure(): ?bool
    {
        return $this->verouilleStructure;
    }

    public function setVerouilleStructure(bool $verouilleStructure): self
    {
        $this->verouilleStructure = $verouilleStructure;

        return $this;
    }

    public function getVerouilleCompetences(): ?bool
    {
        return $this->verouilleCompetences;
    }

    public function setVerouilleCompetences(bool $verouilleCompetences): self
    {
        $this->verouilleCompetences = $verouilleCompetences;

        return $this;
    }

    public function getVerouilleCroise(): ?bool
    {
        return $this->verouilleCroise;
    }

    public function setVerouilleCroise(bool $verouilleCroise): self
    {
        $this->verouilleCroise = $verouilleCroise;

        return $this;
    }

    public function getDateVersionCompetence(): ?DateTimeInterface
    {
        return $this->dateVersionCompetence;
    }

    public function setDateVersionCompetence(?DateTimeInterface $dateVersionCompetence): self
    {
        $this->dateVersionCompetence = $dateVersionCompetence;

        return $this;
    }

    public function getDateVersionFormation(): ?DateTimeInterface
    {
        return $this->dateVersionFormation;
    }

    public function setDateVersionFormation(?DateTimeInterface $dateVersionFormation): self
    {
        $this->dateVersionFormation = $dateVersionFormation;

        return $this;
    }

    public function getPnBloque(): ?bool
    {
        return $this->pn_bloque;
    }

    public function setPnBloque(bool $pn_bloque): self
    {
        $this->pn_bloque = $pn_bloque;

        return $this;
    }

    public function getCoeffEditable(): ?bool
    {
        return $this->coeff_editable;
    }

    public function setCoeffEditable(bool $coeff_editable): self
    {
        $this->coeff_editable = $coeff_editable;

        return $this;
    }

    public function getAltBut1(): ?float
    {
        return $this->altBut1;
    }

    public function setAltBut1(float $altBut1): self
    {
        $this->altBut1 = $altBut1;

        return $this;
    }

    public function getAltBut2(): ?float
    {
        return $this->altBut2;
    }

    public function setAltBut2(float $altBut2): self
    {
        $this->altBut2 = $altBut2;

        return $this;
    }

    public function getAltBut3(): ?float
    {
        return $this->altBut3;
    }

    public function setAltBut3(float $altBut3): self
    {
        $this->altBut3 = $altBut3;

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
            $qapesSae->setSpecialite($this);
        }

        return $this;
    }

    public function removeQapesSae(QapesSae $qapesSae): self
    {
        // set the owning side to null (unless already changed)
        if ($this->qapesSaes->removeElement($qapesSae) && $qapesSae->getSpecialite() === $this) {
            $qapesSae->setSpecialite(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getSigle();
    }
}
