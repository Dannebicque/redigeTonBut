<?php

namespace App\Entity;

use App\Repository\ApcCompetenceSemestreRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApcCompetenceSemestreRepository::class)
 */
class ApcCompetenceSemestre
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ApcCompetence::class, inversedBy="apcCompetenceSemestres")
     */
    private $competence;

    /**
     * @ORM\ManyToOne(targetEntity=Semestre::class, inversedBy="apcCompetenceSemestres")
     */
    private $semestre;

    /**
     * @ORM\Column(type="float")
     */
    private $ECTS;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

        return $this;
    }

    public function getECTS(): ?float
    {
        return $this->ECTS;
    }

    public function setECTS(float $ECTS): self
    {
        $this->ECTS = $ECTS;

        return $this;
    }
}
