<?php

namespace App\Entity;

use App\Repository\ApcSaeParcoursRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApcSaeParcoursRepository::class)
 */
class ApcSaeParcours
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ApcSae::class, inversedBy="apcSaeParcours")
     */
    private $sae;

    /**
     * @ORM\ManyToOne(targetEntity=ApcParcours::class, inversedBy="apcSaeParcours")
     */
    private $parcours;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSae(): ?ApcSae
    {
        return $this->sae;
    }

    public function setSae(?ApcSae $sae): self
    {
        $this->sae = $sae;

        return $this;
    }

    public function getParcours(): ?ApcParcours
    {
        return $this->parcours;
    }

    public function setParcours(?ApcParcours $parcours): self
    {
        $this->parcours = $parcours;

        return $this;
    }
}
