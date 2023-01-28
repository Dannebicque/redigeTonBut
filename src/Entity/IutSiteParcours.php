<?php

namespace App\Entity;

use App\Repository\IutSiteParcoursRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IutSiteParcoursRepository::class)
 */
class IutSiteParcours
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=IutSite::class, inversedBy="iutSiteParcours")
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity=ApcParcours::class, inversedBy="iutSiteParcours")
     */
    private $parcours;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSite(): ?IutSite
    {
        return $this->site;
    }

    public function setSite(?IutSite $site): self
    {
        $this->site = $site;

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
