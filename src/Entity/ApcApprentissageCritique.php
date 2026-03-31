<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcApprentissageCritique.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 13/05/2021 17:00
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcApprentissageCritiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ApcApprentissageCritiqueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ApcApprentissageCritique extends BaseEntity
{
    use LifeCycleTrait;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['read:competence', 'read:ressource', 'read:sae'])]
    private ?string $libelle;

    #[ORM\ManyToOne(targetEntity: ApcNiveau::class, inversedBy: 'apcApprentissageCritiques')]
    private ?ApcNiveau $niveau;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Groups(['read:competence', 'read:ressource', 'read:sae'])]
    private ?string $code = null;

    /**
     * @var Collection<int, ApcRessourceApprentissageCritique>
     */
    #[ORM\OneToMany(mappedBy: 'apprentissageCritique', targetEntity: ApcRessourceApprentissageCritique::class)]
    private Collection $apcRessourceApprentissageCritiques;

    /**
     * @var Collection<int, ApcSaeApprentissageCritique>
     */
    #[ORM\OneToMany(mappedBy: 'apprentissageCritique', targetEntity: ApcSaeApprentissageCritique::class)]
    private Collection $apcSaeApprentissageCritiques;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['read:competence'])]
    private ?int $ordre;

    /**
     * ApcApprentissageCritique constructor.
     *
     * @param ApcNiveau|null $niveau
     */
    public function __construct(?ApcNiveau $niveau = null)
    {
        $this->niveau = $niveau;
        $this->apcRessourceApprentissageCritiques = new ArrayCollection();
        $this->apcSaeApprentissageCritiques = new ArrayCollection();
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

    public function getNiveau(): ?ApcNiveau
    {
        return $this->niveau;
    }

    public function setNiveau(?ApcNiveau $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code = ''): self
    {
        $this->code = trim($code);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcRessourceApprentissageCritiques(): Collection
    {
        return $this->apcRessourceApprentissageCritiques;
    }

    public function addApcRessourceApprentissageCritique(
        ApcRessourceApprentissageCritique $apcRessourceApprentissageCritique
    ): self {
        if (!$this->apcRessourceApprentissageCritiques->contains($apcRessourceApprentissageCritique)) {
            $this->apcRessourceApprentissageCritiques[] = $apcRessourceApprentissageCritique;
            $apcRessourceApprentissageCritique->setApprentissageCritique($this);
        }

        return $this;
    }

    public function removeApcRessourceApprentissageCritique(
        ApcRessourceApprentissageCritique $apcRessourceApprentissageCritique
    ): self {
        // set the owning side to null (unless already changed)
        if ($this->apcRessourceApprentissageCritiques->removeElement($apcRessourceApprentissageCritique) && $apcRessourceApprentissageCritique->getApprentissageCritique() === $this) {
            $apcRessourceApprentissageCritique->setApprentissageCritique(null);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcSaeApprentissageCritiques(): Collection
    {
        return $this->apcSaeApprentissageCritiques;
    }

    public function addApcSaeApprentissageCritique(ApcSaeApprentissageCritique $apcSaeApprentissageCritique): self
    {
        if (!$this->apcSaeApprentissageCritiques->contains($apcSaeApprentissageCritique)) {
            $this->apcSaeApprentissageCritiques[] = $apcSaeApprentissageCritique;
            $apcSaeApprentissageCritique->setApprentissageCritique($this);
        }

        return $this;
    }

    public function removeApcSaeApprentissageCritique(ApcSaeApprentissageCritique $apcSaeApprentissageCritique): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcSaeApprentissageCritiques->removeElement($apcSaeApprentissageCritique) && $apcSaeApprentissageCritique->getApprentissageCritique() === $this) {
            $apcSaeApprentissageCritique->setApprentissageCritique(null);
        }

        return $this;
    }

    public function getCompetence(): ?ApcCompetence
    {
        if ($this->getNiveau() instanceof ApcNiveau) {
            return $this->getNiveau()->getCompetence();
        }

        return null;
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

    public function getVersion(): ?Version
    {
        if ($this->getCompetence() instanceof ApcCompetence)
        {
            return $this->getCompetence()->getVersion();
        }

        return null;
    }

    public function display(): string
    {
        return $this->getCode() . ' | ' . $this->getLibelle();
    }
}
