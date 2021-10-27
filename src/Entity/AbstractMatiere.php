<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/AbstractMatiere.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 31/05/2021 10:45
 */

namespace App\Entity;

use App\Utils\Convert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractMatiere.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractMatiere extends BaseEntity
{
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"matiere"})
     */
    private string $libelle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\Column(type="float")
     * @Groups({"matiere_administration"})
     */
    private float $cmPpn = 0;

    /**
     * @ORM\Column(type="float")
     * @Groups({"matiere_administration"})
     */
    private float $tdPpn = 0;

    /**
     * @ORM\Column(type="float")
     * @Groups({"matiere_administration"})
     */
    private float $heuresTotales = 0;

    /**
     * @ORM\Column(type="float")
     * @Groups({"matiere_administration"})
     */
    private float $tpPpn = 0;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private ?string $commentaire = '';

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"matiere"})
     */
    private ?string $codeMatiere = '-';

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private ?string $libelleCourt;

    public function getCmPpn(): float
    {
        return $this->cmPpn;
    }

    public function setCmPpn(mixed $cmPpn): void
    {
        $this->cmPpn = Convert::convertToFloat($cmPpn);
    }

    public function getHeuresTotales(): float
    {
        return $this->heuresTotales;
    }

    public function setHeuresTotales(mixed $heuresTotales): void
    {
        $this->heuresTotales = Convert::convertToFloat($heuresTotales);
    }

    public function getTdPpn(): float
    {
        return $this->tdPpn;
    }

    public function setTdPpn(mixed $tdPpn): void
    {
        $this->tdPpn = Convert::convertToFloat($tdPpn);
    }

    public function getTpPpn(): float
    {
        return $this->tpPpn;
    }

    public function setTpPpn(mixed $tpPpn): void
    {
        $this->tpPpn = Convert::convertToFloat($tpPpn);
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }

    public function getDisplay(): string
    {
        return $this->getCodeMatiere().' | '.$this->getLibelle();
    }

    public function getCodeMatiere(): ?string
    {
        return $this->codeMatiere;
    }

    public function setCodeMatiere(string $codeMatiere): void
    {
        $this->codeMatiere = trim($codeMatiere);
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle($libelle): void
    {
        $this->libelle = $libelle;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLibelleCourt(): ?string
    {
        return $this->libelleCourt;
    }

    public function setLibelleCourt(?string $libelleCourt): self
    {
        $this->libelleCourt = $libelleCourt;

        return $this;
    }
}
