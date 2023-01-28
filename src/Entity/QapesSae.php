<?php

namespace App\Entity;

use App\Repository\QapesSaeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=QapesSaeRepository::class)
 */
class QapesSae
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="qapesSaesAuteurs")
     * @ORM\JoinTable(name="qapes_sae_auteur")
     */
    private $auteur;

    /**
     * @ORM\ManyToOne(targetEntity=IutSite::class, inversedBy="qapesSaes")
     */
    private $iutSite;

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class, inversedBy="qapesSaes")
     */
    private $specialite;

    /**
     * @ORM\ManyToOne(targetEntity=ApcParcours::class, inversedBy="qapesSaes")
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=ApcSae::class, inversedBy="qapesSaes")
     */
    private $sae;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="qapesSaesRedacteurs")
     * @ORM\JoinTable(name="qapes_sae_redacteur")
     */
    private $redacteur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $intituleSae = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lien  = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $aEpingler  = null;

    /**
     * @ORM\Column(type="integer")
     */
    private $anneeCreation = 0;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $dateVersion;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $modeDispense;

    /**
     * @ORM\Column(type="float")
     */
    private $nbEcts = 0;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $typeSae;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $saeGroupeIndividuelle;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $publicCible;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $publicCibleCommentaire;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbEtudiants = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbEncadrants = 0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $nbHeuresAutonomie = 0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $nbHeuresDirigees;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $objectifsSae;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $deroulementSae;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $lienLigneDuTemps;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $evaluations;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $dateEvaluation;

    /**
     * @ORM\OneToMany(targetEntity=QapesSaeCritereReponse::class, mappedBy="qapes_sae")
     */
    private $qapesSaeCritereReponses;

    public function __construct(UserInterface $user)
    {
        $this->auteur = new ArrayCollection();
        $this->redacteur = new ArrayCollection();
        $this->addRedacteur($user);
        $this->qapesSaeCritereReponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAuteur(): Collection
    {
        return $this->auteur;
    }

    public function addAuteur(User $auteur): self
    {
        if (!$this->auteur->contains($auteur)) {
            $this->auteur[] = $auteur;
        }

        return $this;
    }

    public function removeAuteur(User $auteur): self
    {
        $this->auteur->removeElement($auteur);

        return $this;
    }

    public function getIutSite(): ?IutSite
    {
        return $this->iutSite;
    }

    public function setIutSite(?IutSite $iutSite): self
    {
        $this->iutSite = $iutSite;

        return $this;
    }

    public function getSpecialite(): ?Departement
    {
        return $this->specialite;
    }

    public function setSpecialite(?Departement $specialite): self
    {
        $this->specialite = $specialite;

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

    public function getSae(): ?ApcSae
    {
        return $this->sae;
    }

    public function setSae(?ApcSae $sae): self
    {
        $this->sae = $sae;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getRedacteur(): Collection
    {
        return $this->redacteur;
    }

    public function addRedacteur(User $redacteur): self
    {
        if (!$this->redacteur->contains($redacteur)) {
            $this->redacteur[] = $redacteur;
        }

        return $this;
    }

    public function removeRedacteur(User $redacteur): self
    {
        $this->redacteur->removeElement($redacteur);

        return $this;
    }

    public function getIntituleSae(): ?string
    {
        return $this->intituleSae;
    }

    public function setIntituleSae(?string $intituleSae): self
    {
        $this->intituleSae = $intituleSae;

        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(?string $lien): self
    {
        $this->lien = $lien;

        return $this;
    }

    public function getAEpingler(): ?string
    {
        return $this->aEpingler;
    }

    public function setAEpingler(?string $aEpingler): self
    {
        $this->aEpingler = $aEpingler;

        return $this;
    }

    public function getAnneeCreation(): ?int
    {
        return $this->anneeCreation;
    }

    public function setAnneeCreation(?int $anneeCreation): self
    {
        $this->anneeCreation = $anneeCreation;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getDateVersion(): ?string
    {
        return $this->dateVersion;
    }

    public function setDateVersion(?string $dateVersion): self
    {
        $this->dateVersion = $dateVersion;

        return $this;
    }

    public function getModeDispense(): ?string
    {
        return $this->modeDispense;
    }

    public function setModeDispense(?string $modeDispense): self
    {
        $this->modeDispense = $modeDispense;

        return $this;
    }

    public function getNbEcts(): ?float
    {
        return $this->nbEcts;
    }

    public function setNbEcts(float $nbEcts = 0): self
    {
        $this->nbEcts = $nbEcts;

        return $this;
    }

    public function getTypeSae(): ?string
    {
        return $this->typeSae;
    }

    public function setTypeSae(?string $typeSae): self
    {
        $this->typeSae = $typeSae;

        return $this;
    }

    public function getSaeGroupeIndividuelle(): ?string
    {
        return $this->saeGroupeIndividuelle;
    }

    public function setSaeGroupeIndividuelle(?string $saeGroupeIndividuelle): self
    {
        $this->saeGroupeIndividuelle = $saeGroupeIndividuelle;

        return $this;
    }

    public function getPublicCible(): ?string
    {
        return $this->publicCible;
    }

    public function setPublicCible(?string $publicCible): self
    {
        $this->publicCible = $publicCible;

        return $this;
    }

    public function getPublicCibleCommentaire(): ?string
    {
        return $this->publicCibleCommentaire;
    }

    public function setPublicCibleCommentaire(?string $publicCibleCommentaire): self
    {
        $this->publicCibleCommentaire = $publicCibleCommentaire;

        return $this;
    }

    public function getNbEtudiants(): ?int
    {
        return $this->nbEtudiants;
    }

    public function setNbEtudiants(int $nbEtudiants = 0): self
    {
        $this->nbEtudiants = $nbEtudiants;

        return $this;
    }

    public function getNbEncadrants(): ?int
    {
        return $this->nbEncadrants;
    }

    public function setNbEncadrants(int $nbEncadrants): self
    {
        $this->nbEncadrants = $nbEncadrants;

        return $this;
    }

    public function getNbHeuresAutonomie(): ?float
    {
        return $this->nbHeuresAutonomie;
    }

    public function setNbHeuresAutonomie(?float $nbHeuresAutonomie): self
    {
        $this->nbHeuresAutonomie = $nbHeuresAutonomie;

        return $this;
    }

    public function getNbHeuresDirigees(): ?float
    {
        return $this->nbHeuresDirigees;
    }

    public function setNbHeuresDirigees(?float $nbHeuresDirigees): self
    {
        $this->nbHeuresDirigees = $nbHeuresDirigees;

        return $this;
    }

    public function getObjectifsSae(): ?string
    {
        return $this->objectifsSae;
    }

    public function setObjectifsSae(?string $objectifsSae): self
    {
        $this->objectifsSae = $objectifsSae;

        return $this;
    }

    public function getDeroulementSae(): ?string
    {
        return $this->deroulementSae;
    }

    public function setDeroulementSae(?string $deroulementSae): self
    {
        $this->deroulementSae = $deroulementSae;

        return $this;
    }

    public function getLienLigneDuTemps(): ?string
    {
        return $this->lienLigneDuTemps;
    }

    public function setLienLigneDuTemps(?string $lienLigneDuTemps): self
    {
        $this->lienLigneDuTemps = $lienLigneDuTemps;

        return $this;
    }

    public function getEvaluations(): ?string
    {
        return $this->evaluations;
    }

    public function setEvaluations(?string $evaluations): self
    {
        $this->evaluations = $evaluations;

        return $this;
    }

    public function getDateEvaluation(): ?string
    {
        return $this->dateEvaluation;
    }

    public function setDateEvaluation(?string $dateEvaluation): self
    {
        $this->dateEvaluation = $dateEvaluation;

        return $this;
    }

    /**
     * @return Collection<int, QapesSaeCritereReponse>
     */
    public function getQapesSaeCritereReponses(): Collection
    {
        return $this->qapesSaeCritereReponses;
    }

    public function addQapesSaeCritereReponse(QapesSaeCritereReponse $qapesSaeCritereReponse): self
    {
        if (!$this->qapesSaeCritereReponses->contains($qapesSaeCritereReponse)) {
            $this->qapesSaeCritereReponses[] = $qapesSaeCritereReponse;
            $qapesSaeCritereReponse->setQapesSae($this);
        }

        return $this;
    }

    public function removeQapesSaeCritereReponse(QapesSaeCritereReponse $qapesSaeCritereReponse): self
    {
        if ($this->qapesSaeCritereReponses->removeElement($qapesSaeCritereReponse)) {
            // set the owning side to null (unless already changed)
            if ($qapesSaeCritereReponse->getQapesSae() === $this) {
                $qapesSaeCritereReponse->setQapesSae(null);
            }
        }

        return $this;
    }
}
