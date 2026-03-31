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
use App\Repository\DepartementRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DepartementRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(SearchFilter::class, properties: ["sigle" => "exact"])]
#[ApiResource(
    collectionOperations: ["get"],
    itemOperations: ["get"],
    normalizationContext: ["groups" => ["read:departement"]]
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

    public const string TERTIAIRE = 'tertiaire';

    public const string SECONDAIRE = 'secondaire';

    public const string TYPE1 = 'type1';

    public const string TYPE2 = 'type2';

    public const string TYPE3 = 'type3';

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(["actualite_administration", "read:departement"])]
    private ?string $libelle;

    /**
     * @var Collection<int, Annee>
     */
    //todo: a supprimer, sur version
    #[ORM\OneToMany(mappedBy: "departement", targetEntity: Annee::class)]
    /** @deprecated */
    private Collection $annees;

    /**
     * @var Collection<int, ApcCompetence>
     */
    #[ORM\OneToMany(mappedBy: "departement", targetEntity: ApcCompetence::class)]
    #[ORM\OrderBy(["couleur" => "ASC"])]
    #[Groups(["read:departement"])]
    /** @deprecated */
    //todo: a supprimer, sur version
    private Collection $apcCompetences;

    /**
     * @var Collection<int, ApcParcours>
     */
    #[ORM\OneToMany(mappedBy: "departement", targetEntity: ApcParcours::class)]
    #[ORM\OrderBy(["ordre" => "ASC"])]
    #[Groups(["read:departement"])]
    /** @deprecated */
    //todo: a supprimer, sur version
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
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(mappedBy: "departement", targetEntity: User::class)]
    private Collection $users;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["read:departement"])]
    //todo: a supprimer, sur version
        /** @deprecated */
    private ?string $textePresentation;

    #[ORM\ManyToOne(targetEntity: Departement::class, inversedBy: "departementEnfants")]
    //todo: pas utilisé ?
    private ?Departement $departement_parent = null;

    /**
     * @var Collection<int, Departement>
     */
    #[ORM\OneToMany(mappedBy: "departement_parent", targetEntity: Departement::class)]
    //todo: pas utilisé ?
    private Collection $departementEnfants;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "CpnDepartements")]
    private Collection $cpns;

    #[ORM\Column(type: Types::BOOLEAN)]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private bool $verouilleStructure;

    #[ORM\Column(type: Types::BOOLEAN)]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private bool $verouilleCompetences;

    #[ORM\Column(type: Types::BOOLEAN)]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private bool $verouilleCroise;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["read:departement"])]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private ?DateTime $dateVersionCompetence;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["read:departement"])]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private ?DateTime $dateVersionFormation;

    #[ORM\Column(type: Types::BOOLEAN)]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private ?bool $pn_bloque = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private ?bool $coeff_editable = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(["read:departement"])]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private ?float $altBut1 = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(["read:departement"])]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private ?float $altBut2 = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(["read:departement"])]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private ?float $altBut3 = null;

    /**
     * @var Collection<int, QapesSae>
     */
    #[ORM\OneToMany(mappedBy: "specialite", targetEntity: QapesSae::class)]
    //todo: a supprimé, déplacé sur Version . A traiter
    private Collection $qapesSaes;

    #[ORM\Column]
    //todo: a supprimé, déplacé sur Version
        /** @deprecated */
    private ?bool $verouilleTextes = true;

    /**
     * @var Collection<int, Version>
     */
    #[ORM\OneToMany(mappedBy: 'departement', targetEntity: Version::class)]
    private Collection $versions;



    public function __construct()
    {
        $this->annees = new ArrayCollection();
        $this->apcCompetences = new ArrayCollection();
        $this->apcParcours = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->departementEnfants = new ArrayCollection();
        $this->cpns = new ArrayCollection();
        $this->qapesSaes = new ArrayCollection();
        $this->versions = new ArrayCollection();
        $this->dateVersionCompetence = new DateTime();
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
    /** @deprecated */
    public function getAnnees(): Collection
    {
        return $this->annees;
    }

    /** @deprecated */
    public function addAnnee(Annee $annee): self
    {
        if (!$this->annees->contains($annee)) {
            $this->annees[] = $annee;
            $annee->setDepartement($this);
        }

        return $this;
    }

    /** @deprecated */
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
    /** @deprecated */
    public function getApcCompetences(): Collection
    {
        return $this->apcCompetences;
    }

    /** @deprecated */
    public function addApcCompetence(ApcCompetence $apcCompetence): self
    {
        if (!$this->apcCompetences->contains($apcCompetence)) {
            $this->apcCompetences[] = $apcCompetence;
            $apcCompetence->setDepartement($this);
        }

        return $this;
    }

    /** @deprecated */
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
    /** @deprecated */
    public function getApcParcours(): Collection
    {
        return $this->apcParcours;
    }

    /** @deprecated */
    public function addApcParcour(ApcParcours $apcParcour): self
    {
        if (!$this->apcParcours->contains($apcParcour)) {
            $this->apcParcours[] = $apcParcour;
            $apcParcour->setDepartement($this);
        }

        return $this;
    }

    /** @deprecated */
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
     * @return Collection
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

    /** @deprecated */
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

    /** @deprecated */
    public function getTextePresentation(): ?string
    {
        return $this->textePresentation;
    }

    /** @deprecated */
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
     * @return Collection
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
     * @return Collection
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

    /** @deprecated */
    public function getVerouilleStructure(): ?bool
    {
        return $this->verouilleStructure;
    }

    /** @deprecated */
    public function setVerouilleStructure(bool $verouilleStructure): self
    {
        $this->verouilleStructure = $verouilleStructure;

        return $this;
    }

    /** @deprecated */
    public function getVerouilleCompetences(): ?bool
    {
        return $this->verouilleCompetences;
    }

    /** @deprecated */
    public function setVerouilleCompetences(bool $verouilleCompetences): self
    {
        $this->verouilleCompetences = $verouilleCompetences;

        return $this;
    }

    /** @deprecated */
    public function getVerouilleCroise(): ?bool
    {
        return $this->verouilleCroise;
    }

    /** @deprecated */
    public function setVerouilleCroise(bool $verouilleCroise): self
    {
        $this->verouilleCroise = $verouilleCroise;

        return $this;
    }

    /** @deprecated */
    public function getDateVersionCompetence(): ?DateTimeInterface
    {
        return $this->dateVersionCompetence;
    }

    /** @deprecated */
    public function setDateVersionCompetence(?DateTimeInterface $dateVersionCompetence): self
    {
        $this->dateVersionCompetence = $dateVersionCompetence;

        return $this;
    }

    /** @deprecated */
    public function getDateVersionFormation(): ?DateTimeInterface
    {
        return $this->dateVersionFormation;
    }

    /** @deprecated */
    public function setDateVersionFormation(?DateTimeInterface $dateVersionFormation): self
    {
        $this->dateVersionFormation = $dateVersionFormation;

        return $this;
    }

    /** @deprecated */
    public function getPnBloque(): ?bool
    {
        return $this->pn_bloque;
    }

    /** @deprecated */
    public function setPnBloque(bool $pn_bloque): self
    {
        $this->pn_bloque = $pn_bloque;

        return $this;
    }

    /** @deprecated */
    public function getCoeffEditable(): ?bool
    {
        return $this->coeff_editable;
    }

    /** @deprecated */
    public function setCoeffEditable(bool $coeff_editable): self
    {
        $this->coeff_editable = $coeff_editable;

        return $this;
    }

    /** @deprecated */
    public function getAltBut1(): ?float
    {
        return $this->altBut1;
    }

    /** @deprecated */
    public function setAltBut1(float $altBut1): self
    {
        $this->altBut1 = $altBut1;

        return $this;
    }

    /** @deprecated */
    public function getAltBut2(): ?float
    {
        return $this->altBut2;
    }

    /** @deprecated */
    public function setAltBut2(float $altBut2): self
    {
        $this->altBut2 = $altBut2;

        return $this;
    }

    /** @deprecated */
    public function getAltBut3(): ?float
    {
        return $this->altBut3;
    }

    /** @deprecated */
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

    /** @deprecated */
    public function isVerouilleTextes(): ?bool
    {
        return $this->verouilleTextes;
    }

    /** @deprecated */
    public function setVerouilleTextes(bool $verouilleTextes): static
    {
        $this->verouilleTextes = $verouilleTextes;

        return $this;
    }

    /**
     * @return Collection<int, Version>
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(Version $version): static
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setDepartement($this);
        }

        return $this;
    }

    public function removeVersion(Version $version): static
    {
        if ($this->versions->removeElement($version)) {
            // set the owning side to null (unless already changed)
            if ($version->getDepartement() === $this) {
                $version->setDepartement(null);
            }
        }

        return $this;
    }
}
