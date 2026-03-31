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
use App\Repository\AnneeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AnneeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Annee extends BaseEntity
{
    use LifeCycleTrait;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $codeEtape;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['read:competence', 'read:departement'])]
    private ?string $libelle;

    #[ORM\Column(name: 'ordre', type: Types::INTEGER)]
    private int $ordre = 1;

    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    #[Groups(['annee'])]
    private ?string $libelleLong;

    /**
     * @var Collection<int, Semestre>
     */
    #[ORM\OneToMany(mappedBy: 'annee', targetEntity: Semestre::class)]
    #[ORM\OrderBy(['ordreLmd' => 'ASC'])]
    private Collection $semestres;

    /**
     * @var Collection<int, ApcNiveau>
     */
    #[ORM\OneToMany(mappedBy: 'annee', targetEntity: ApcNiveau::class)]
    private Collection $apcNiveaux;

    #[ORM\ManyToOne(targetEntity: Departement::class, inversedBy: 'annees')]
    private ?Departement $departement;

    #[ORM\ManyToOne(inversedBy: 'annees')]
    private ?Version $version = null;

    public function __construct()
    {
        $this->semestres = new ArrayCollection();
        $this->apcNiveaux = new ArrayCollection();
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): void
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
     * @return Collection
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

    public function setCodeEtape(?string $codeEtape): void
    {
        $this->codeEtape = $codeEtape;
    }

    /** @deprecated */
    public function getDepartement(): ?Departement
    {
        //todo: a adapter pour passer par Version
        return $this->departement;
    }

    /** @deprecated */
    public function setDepartement(?Departement $departement): self
    {
        $this->departement = $departement;

        return $this;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function setVersion(?Version $version): static
    {
        $this->version = $version;

        return $this;
    }
}
