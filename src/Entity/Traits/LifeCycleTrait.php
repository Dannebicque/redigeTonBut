<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/Traits/LifeCycleTrait.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 05/06/2021 11:21
 */

namespace App\Entity\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait LifeCycleTrait.
 */
trait LifeCycleTrait
{
    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $created;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $updated;

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created ?? new DateTime();
    }

    public function setCreated(?DateTimeInterface $created): void
    {
        $this->created = $created;
    }

    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated ?? new DateTime();
    }

    public function setUpdated(?DateTimeInterface $updated): void
    {
        $this->updated = $updated;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setUpdatedValue(): void
    {
        $this->updated = new DateTime();
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreatedValue(): void
    {
        $this->created = new DateTime();
    }
}
