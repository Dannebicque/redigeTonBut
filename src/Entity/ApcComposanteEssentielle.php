<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcComposanteEssentielle.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 12/03/2021 22:10
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcComposanteEssentielleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ApcComposanteEssentielleRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ApcComposanteEssentielle extends BaseEntity
{
    use LifeCycleTrait;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:competence"})
     */
    private ?string $libelle;

    /**
     * @ORM\ManyToOne(targetEntity=ApcCompetence::class, inversedBy="apcComposanteEssentielles")
     */
    private ?ApcCompetence $competence;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read:competence"})
     */
    private ?int $ordre;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"read:competence"})
     */
    private ?string $code;

    public function __construct(?ApcCompetence $competence = null)
    {
        $this->competence = $competence;
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

    public function getCompetence(): ?ApcCompetence
    {
        return $this->competence;
    }

    public function setCompetence(?ApcCompetence $competence): self
    {
        $this->competence = $competence;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDepartement(): ?Departement
    {
        return $this->getCompetence()?->getDepartement();
    }
}
