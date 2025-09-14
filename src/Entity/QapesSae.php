<?php

namespace App\Entity;

use App\Repository\QapesSaeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: QapesSaeRepository::class)]
class QapesSae
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    public $qapesSaeCritereReponses;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'qapesSaesAuteurs')]
    #[ORM\JoinTable(name: 'qapes_sae_auteur')]
    private \Doctrine\Common\Collections\Collection $auteur;

    #[ORM\ManyToOne(targetEntity: IutSite::class, inversedBy: 'qapesSaes')]
    private ?\App\Entity\IutSite $iutSite = null;

    #[ORM\ManyToOne(targetEntity: Departement::class, inversedBy: 'qapesSaes')]
    private ?\App\Entity\Departement $specialite = null;

    #[ORM\ManyToOne(targetEntity: ApcParcours::class, inversedBy: 'qapesSaes')]
    private ?\App\Entity\ApcParcours $parcours = null;

    #[ORM\ManyToOne(targetEntity: ApcSae::class, inversedBy: 'qapesSaes')]
    private ?\App\Entity\ApcSae $sae = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'qapesSaesRedacteurs')]
    #[ORM\JoinTable(name: 'qapes_sae_redacteur')]
    private \Doctrine\Common\Collections\Collection $redacteur;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    private ?string $intituleSae = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    private ?string $lien  = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $aEpingler  = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $anneeCreation = 0;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 5, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 100, nullable: true)]
    private ?string $dateVersion = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 20, nullable: true)]
    private ?string $modeDispense = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::FLOAT)]
    private ?float $nbEcts = 0;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 20, nullable: true)]
    private ?string $typeSae = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 20, nullable: true)]
    private ?string $saeGroupeIndividuelle = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 20, nullable: true)]
    private ?string $publicCible = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $publicCibleCommentaire = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $nbEtudiants = 0;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $nbEncadrants = 0;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::FLOAT, nullable: true)]
    private ?float $nbHeuresAutonomie = 0;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::FLOAT, nullable: true)]
    private ?float $nbHeuresDirigees = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $objectifsSae = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $deroulementSae = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $lienLigneDuTemps = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $evaluations = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 20, nullable: true)]
    private ?string $dateEvaluation = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\QapesSaeCritereReponse>
     */
    #[ORM\OneToMany(targetEntity: QapesSaeCritereReponse::class, mappedBy: 'sae', cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $qapesSaeCritereReponse;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $effetsObserves = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    private ?string $lienRepertoire = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $coordinationIntervenant = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $isCoordinationIntervenant = false;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $lienDocumentCoordination = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $consignesCommuniquees = false;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $lienConsignes = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $elementsContexte = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $elementsContextesObstacles = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $swatForce = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $swatFaiblesse = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $modificationsApportees = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $temoignagesEtudiants = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $temoignagesEnseignants = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $publiee = false;

    public function __construct(UserInterface $user)
    {
        $this->auteur = new ArrayCollection();
        $this->redacteur = new ArrayCollection();
        $this->addRedacteur($user);
        $this->qapesSaeCritereReponses = new ArrayCollection();
        $this->qapesSaeCritereReponse = new ArrayCollection();
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
    public function getQapesSaeCritereReponse(): Collection
    {
        return $this->qapesSaeCritereReponse;
    }

    public function addQapesSaeCritereReponse(QapesSaeCritereReponse $qapesSaeCritereReponse): self
    {
        if (!$this->qapesSaeCritereReponse->contains($qapesSaeCritereReponse)) {
            $this->qapesSaeCritereReponse[] = $qapesSaeCritereReponse;
            $qapesSaeCritereReponse->setSae($this);
        }

        return $this;
    }

    public function removeQapesSaeCritereReponse(QapesSaeCritereReponse $qapesSaeCritereReponse): self
    {
        // set the owning side to null (unless already changed)
        if ($this->qapesSaeCritereReponse->removeElement($qapesSaeCritereReponse) && $qapesSaeCritereReponse->getSae() === $this) {
            $qapesSaeCritereReponse->setSae(null);
        }

        return $this;
    }

    public function getEffetsObserves(): ?string
    {
        return $this->effetsObserves;
    }

    public function setEffetsObserves(?string $effetsObserves): self
    {
        $this->effetsObserves = $effetsObserves;

        return $this;
    }

    public function getLienRepertoire(): ?string
    {
        return $this->lienRepertoire;
    }

    public function setLienRepertoire(?string $lienRepertoire): self
    {
        $this->lienRepertoire = $lienRepertoire;

        return $this;
    }

    public function getCoordinationIntervenant(): ?string
    {
        return $this->coordinationIntervenant;
    }

    public function setCoordinationIntervenant(?string $coordinationIntervenant): self
    {
        $this->coordinationIntervenant = $coordinationIntervenant;

        return $this;
    }

    public function getIsCoordinationIntervenant(): ?bool
    {
        return $this->isCoordinationIntervenant;
    }

    public function setIsCoordinationIntervenant(bool $isCoordinationIntervenant): self
    {
        $this->isCoordinationIntervenant = $isCoordinationIntervenant;

        return $this;
    }

    public function getLienDocumentCoordination(): ?string
    {
        return $this->lienDocumentCoordination;
    }

    public function setLienDocumentCoordination(?string $lienDocumentCoordination): self
    {
        $this->lienDocumentCoordination = $lienDocumentCoordination;

        return $this;
    }

    public function getConsignesCommuniquees(): ?bool
    {
        return $this->consignesCommuniquees;
    }

    public function setConsignesCommuniquees(bool $consignesCommuniquees): self
    {
        $this->consignesCommuniquees = $consignesCommuniquees;

        return $this;
    }

    public function getLienConsignes(): ?string
    {
        return $this->lienConsignes;
    }

    public function setLienConsignes(?string $lienConsignes): self
    {
        $this->lienConsignes = $lienConsignes;

        return $this;
    }

    public function getElementsContexte(): ?string
    {
        return $this->elementsContexte;
    }

    public function setElementsContexte(?string $elementsContexte): self
    {
        $this->elementsContexte = $elementsContexte;

        return $this;
    }

    public function getElementsContextesObstacles(): ?string
    {
        return $this->elementsContextesObstacles;
    }

    public function setElementsContextesObstacles(?string $elementsContextesObstacles): self
    {
        $this->elementsContextesObstacles = $elementsContextesObstacles;

        return $this;
    }

    public function getSwatForce(): ?string
    {
        return $this->swatForce;
    }

    public function setSwatForce(?string $swatForce): self
    {
        $this->swatForce = $swatForce;

        return $this;
    }

    public function getSwatFaiblesse(): ?string
    {
        return $this->swatFaiblesse;
    }

    public function setSwatFaiblesse(?string $swatFaiblesse): self
    {
        $this->swatFaiblesse = $swatFaiblesse;

        return $this;
    }

    public function getModificationsApportees(): ?string
    {
        return $this->modificationsApportees;
    }

    public function setModificationsApportees(?string $modificationsApportees): self
    {
        $this->modificationsApportees = $modificationsApportees;

        return $this;
    }

    public function getTemoignagesEtudiants(): ?string
    {
        return $this->temoignagesEtudiants;
    }

    public function setTemoignagesEtudiants(?string $temoignagesEtudiants): self
    {
        $this->temoignagesEtudiants = $temoignagesEtudiants;

        return $this;
    }

    public function getTemoignagesEnseignants(): ?string
    {
        return $this->temoignagesEnseignants;
    }

    public function setTemoignagesEnseignants(?string $temoignagesEnseignants): self
    {
        $this->temoignagesEnseignants = $temoignagesEnseignants;

        return $this;
    }

    public function isPubliee(): bool
    {
        return $this->publiee;
    }

    public function setPubliee(bool $publiee): void
    {
        $this->publiee = $publiee;
    }


}
