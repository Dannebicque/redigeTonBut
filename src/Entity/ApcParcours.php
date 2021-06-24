<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcParcours.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 02/06/2021 14:02
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcParcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApcParcoursRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ApcParcours extends BaseEntity
{
    use LifeCycleTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $libelle;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private ?string $code;

    /**
     * @ORM\OneToMany(targetEntity=ApcParcoursNiveau::class, mappedBy="parcours")
     */
    private Collection $apcParcoursNiveaux;

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class, inversedBy="apcParcours")
     */
    private ?Departement $departement;

    public function __construct(Departement $departement)
    {
        $this->setDepartement($departement);
        $this->apcParcoursNiveaux = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = trim($code);

        return $this;
    }

    /**
     * @return Collection|ApcParcoursNiveau[]
     */
    public function getApcParcoursNiveaux(): Collection
    {
        return $this->apcParcoursNiveaux;
    }

    public function addApcParcoursNiveau(ApcParcoursNiveau $apcParcoursNiveaux): self
    {
        if (!$this->apcParcoursNiveaux->contains($apcParcoursNiveaux)) {
            $this->apcParcoursNiveaux[] = $apcParcoursNiveaux;
            $apcParcoursNiveaux->setParcours($this);
        }

        return $this;
    }

    public function removeApcParcoursNiveau(ApcParcoursNiveau $apcParcoursNiveaux): self
    {
        if ($this->apcParcoursNiveaux->removeElement($apcParcoursNiveaux)) {
            // set the owning side to null (unless already changed)
            if ($apcParcoursNiveaux->getParcours() === $this) {
                $apcParcoursNiveaux->setParcours(null);
            }
        }

        return $this;
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
