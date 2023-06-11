<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcParcoursNiveau.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 02/06/2021 14:02
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcParcoursNiveauRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ApcParcoursNiveauRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ApcParcoursNiveau extends BaseEntity
{
    use LifeCycleTrait;

    /**
     * @ORM\ManyToOne(targetEntity=ApcNiveau::class, inversedBy="apcParcoursNiveaux")
     * @Groups({"read:departement"})
     */
    private ?ApcNiveau $niveau;

    /**
     * @ORM\ManyToOne(targetEntity=ApcParcours::class, inversedBy="apcParcoursNiveaux")
     */
    private ?ApcParcours $parcours;

    public function getNiveau(): ?ApcNiveau
    {
        return $this->niveau;
    }

    public function setNiveau(?ApcNiveau $niveau): self
    {
        $this->niveau = $niveau;

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
