<?php

namespace App\Entity;

use App\Repository\ApcRessourceParcoursRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ApcRessourceParcoursRepository::class)
 */
class ApcRessourceParcours
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ApcRessource::class, inversedBy="apcRessourceParcours")
     */
    private $ressource;

    /**
     * @ORM\ManyToOne(targetEntity=ApcParcours::class, inversedBy="apcRessourceParcours")
     * @Groups({"read:ressource"})
     */
    private $parcours;

    /**
     * ApcRessourceParcours constructor.
     *
     * @param $ressource
     * @param $parcours
     */
    public function __construct($ressource, $parcours)
    {
        $this->ressource = $ressource;
        $this->parcours = $parcours;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRessource(): ?ApcRessource
    {
        return $this->ressource;
    }

    public function setRessource(?ApcRessource $ressource): self
    {
        $this->ressource = $ressource;

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
