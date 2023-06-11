<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcRessourceApprentissageCritique.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 20/03/2021 15:50
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcRessourceApprentissageCritiqueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ApcRessourceApprentissageCritiqueRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ApcRessourceApprentissageCritique extends BaseEntity
{
    use LifeCycleTrait;

    /**
     * @ORM\ManyToOne(targetEntity=ApcRessource::class, inversedBy="apcRessourceApprentissageCritiques")
     */
    private ?ApcRessource $ressource;

    /**
     * @ORM\ManyToOne(targetEntity=ApcApprentissageCritique::class, inversedBy="apcRessourceApprentissageCritiques")
     * @ORM\OrderBy({"code":"ASC"})
     * @Groups({"read:ressource"})
     */
    private ?ApcApprentissageCritique $apprentissageCritique;

    public function __construct(ApcRessource $ressource, ApcApprentissageCritique $apprentissageCritique)
    {
        $this->ressource = $ressource;
        $this->apprentissageCritique = $apprentissageCritique;
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

    public function getApprentissageCritique(): ?ApcApprentissageCritique
    {
        return $this->apprentissageCritique;
    }

    public function setApprentissageCritique(?ApcApprentissageCritique $apprentissageCritique): self
    {
        $this->apprentissageCritique = $apprentissageCritique;

        return $this;
    }
}
