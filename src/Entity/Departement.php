<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/Departement.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 05/06/2021 12:02
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DepartementRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Departement extends BaseEntity
{
    use LifeCycleTrait;

    public const TERTIAIRE = 'tertiaire';
    public const SECONDAIRE = 'secondaire';
public const TYPE1 = 'type1';
public const TYPE2 = 'type2';
public const TYPE3 = 'type3';

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"actualite_administration"})
     */
    private ?string $libelle;

    /**
     * @ORM\OneToMany(targetEntity=Annee::class, mappedBy="departement")
     */
    private $annees;

    /**
     * @ORM\OneToMany(targetEntity=ApcCompetence::class, mappedBy="departement")
     */
    private $apcCompetences;

    /**
     * @ORM\OneToMany(targetEntity=ApcParcours::class, mappedBy="departement")
     */
    private $apcParcours;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $typeDepartement;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $sigle;

    /**
     * @ORM\Column(type="integer")
     */
    private $numeroAnnexe;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $typeStructure;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="departement")
     */
    private $users;

    public function __construct()
    {
        $this->annees = new ArrayCollection();
        $this->apcCompetences = new ArrayCollection();
        $this->apcParcours = new ArrayCollection();
        $this->users = new ArrayCollection();
    }



    /**
     * @return string
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle($libelle): void
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
        if ($this->annees->removeElement($annee)) {
            // set the owning side to null (unless already changed)
            if ($annee->getDepartement() === $this) {
                $annee->setDepartement(null);
            }
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
        if ($this->apcCompetences->removeElement($apcCompetence)) {
            // set the owning side to null (unless already changed)
            if ($apcCompetence->getDepartement() === $this) {
                $apcCompetence->setDepartement(null);
            }
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
        if ($this->apcParcours->removeElement($apcParcour)) {
            // set the owning side to null (unless already changed)
            if ($apcParcour->getDepartement() === $this) {
                $apcParcour->setDepartement(null);
            }
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

    public function getCpn(): ?User
    {
        return $this->cpn;
    }

    public function setCpn(?User $cpn): self
    {
        $this->cpn = $cpn;

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
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getDepartement() === $this) {
                $user->setDepartement(null);
            }
        }

        return $this;
    }
}
