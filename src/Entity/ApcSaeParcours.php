<?php

namespace App\Entity;

use App\Repository\ApcSaeParcoursRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ApcSaeParcoursRepository::class)
 */
class ApcSaeParcours
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:sae"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ApcSae::class, inversedBy="apcSaeParcours")
     */
    private ApcSae $sae;

    /**
     * @ORM\ManyToOne(targetEntity=ApcParcours::class, inversedBy="apcSaeParcours")
     * @Groups({"read:sae"})
     */
    private ApcParcours $parcours;

    public function __construct(ApcSae $sae, ApcParcours $parcours)
    {
        $this->sae = $sae;
        $this->parcours = $parcours;
    }


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
