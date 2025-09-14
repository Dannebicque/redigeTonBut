<?php

namespace App\Entity;

use App\Repository\ApcCompetenceSemestreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApcCompetenceSemestreRepository::class)]
class ApcCompetenceSemestre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ApcCompetence::class, inversedBy: 'apcCompetenceSemestres')]
    private ?ApcCompetence $competence = null;

    #[ORM\ManyToOne(targetEntity: Semestre::class, inversedBy: 'apcCompetenceSemestres')]
    private ?Semestre $semestre = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $ECTS = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $ectsParcours = "";

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

    public function getEctsParcours(): ?array
    {
        return json_decode($this->ectsParcours, true);
    }

    public function setEctsParcours(array $ectsParcours): self
    {
        $this->ectsParcours = json_encode($ectsParcours);

        return $this;
    }
}
