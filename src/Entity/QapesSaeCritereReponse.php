<?php

namespace App\Entity;

use App\Repository\QapesSaeCritereReponseRepository;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\ManyToOne(targetEntity=QapesSae::class, inversedBy="qapesSaeCritereReponses")
     */
    private $qapes_sae;

    /**
     * @ORM\ManyToOne(targetEntity=QapesCriteresEvaluation::class, inversedBy="qapesSaeCritereReponses")
     */
    private $qapes_critere;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reponse;

    /**
     * @ORM\Column(type="text")
     */
    private $commentaire_repose;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQapesSae(): ?QapesSae
    {
        return $this->qapes_sae;
    }

    public function setQapesSae(?QapesSae $qapes_sae): self
    {
        $this->qapes_sae = $qapes_sae;

        return $this;
    }

    public function getQapesCritere(): ?QapesCriteresEvaluation
    {
        return $this->qapes_critere;
    }

    public function setQapesCritere(?QapesCriteresEvaluation $qapes_critere): self
    {
        $this->qapes_critere = $qapes_critere;

        return $this;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(string $reponse): self
    {
        $this->reponse = $reponse;

        return $this;
    }

    public function getCommentaireRepose(): ?string
    {
        return $this->commentaire_repose;
    }

    public function setCommentaireRepose(string $commentaire_repose): self
    {
        $this->commentaire_repose = $commentaire_repose;

        return $this;
    }
}
