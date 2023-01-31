<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\QapesSaeCritereReponseRepository;

/**
 * @ORM\Entity(repositoryClass=QapesSaeCritereReponseRepository::class)
 */
class QapesSaeCritereReponse
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=QapesCritere::class, inversedBy="qapesSaeCritereReponses")
     */
    private $critere;

    /**
     * @ORM\ManyToOne(targetEntity=QapesSae::class, inversedBy="qapesSaeCritereReponse")
     */
    private $sae;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @ORM\ManyToOne(targetEntity=QapesCritereReponse::class, inversedBy="qapesSaeCritereReponses")
     */
    private $reponse;

    public function getCritere(): ?QapesCritere
    {
        return $this->critere;
    }

    public function setCritere(?QapesCritere $critere): self
    {
        $this->critere = $critere;

        return $this;
    }

    public function getSae(): ?QapesSae
    {
        return $this->sae;
    }

    public function setSae(?QapesSae $sae): self
    {
        $this->sae = $sae;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getReponse(): ?QapesCritereReponse
    {
        return $this->reponse;
    }

    public function setReponse(?QapesCritereReponse $reponse): self
    {
        $this->reponse = $reponse;

        return $this;
    }
}
