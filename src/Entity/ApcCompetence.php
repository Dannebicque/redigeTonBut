<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcCompetence.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 13/05/2021 17:00
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcComptenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\api\GetCompetenceSpecialite;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read:competence"}},
 *     collectionOperations={
 *     "get",
 *     "get_by_specialite"={
 *         "method"="GET",
 *         "path"="/specialite/{specialite}/competences",
 *         "defaults"={"annee"=2022},
 *         "openapi_context" = {
 *             "parameters" = {
 *                 {
 *                      "name" = "specialite",
 *                      "in" = "path",
 *                      "description" = "Spécialité",
 *                      "required" = true,
 *                      "schema"={
 *                          "type" : "string"
 *                      },
 *                      "style"="simple"
 *                 },
 *     {
 * "name" = "annee",
 * "in" = "query",
 * "description" = "Année",
 * "required" = false,
 * "schema"={
 * "type" : "integer",
 * "default" : 2022
 * }
 * }
 *           }
 *     },
 *         "controller"=GetCompetenceSpecialite::class,
 *     }},
 *     itemOperations={"get"}
 * )
 */
#[ORM\Entity(repositoryClass: ApcComptenceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ApcCompetence extends BaseEntity
{
    use LifeCycleTrait;

    public const array COLOREXCEl =
        [
            'c1' => '009C2B26',
            'c2' => '00D07740',
            'c3' => '00E5B94D',
            'c4' => '00E5B94D',
            'c5' => '002B4C76',
            'c6' => '007F1F53',
        ];

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['read:competence', 'read:ressource', 'read:sae'])]
    private ?string $libelle;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Groups(['read:competence', 'read:departement', 'read:ressource', 'read:sae'])]
    private ?string $nom_court;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Groups(['read:competence'])]
    private ?string $couleur;

    /**
     * @var Collection<int, ApcComposanteEssentielle>
     */
    #[ORM\OneToMany(mappedBy: 'competence', targetEntity: ApcComposanteEssentielle::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:competence'])]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $apcComposanteEssentielles;

    /**
     * @var Collection<int, ApcNiveau>
     */
    #[ORM\OneToMany(mappedBy: 'competence', targetEntity: ApcNiveau::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:competence'])]
    private Collection $apcNiveaux;

    /**
     * @var Collection<int, ApcRessourceCompetence>
     */
    #[ORM\OneToMany(mappedBy: 'competence', targetEntity: ApcRessourceCompetence::class)]
    private Collection $apcRessourceCompetences;

    /**
     * @var Collection<int, ApcSaeCompetence>
     */
    #[ORM\OneToMany(mappedBy: 'competence', targetEntity: ApcSaeCompetence::class)]
    private Collection $apcSaeCompetences;

    /**
     * @var Collection<int, ApcSituationProfessionnelle>
     */
    #[ORM\OneToMany(mappedBy: 'competence', targetEntity: ApcSituationProfessionnelle::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:competence'])]
    private Collection $apcSituationProfessionnelles;

    #[ORM\ManyToOne(targetEntity: Departement::class, inversedBy: 'apcCompetences')]
    private ?Departement $departement;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['read:competence', 'read:departement', 'read:ressource', 'read:sae'])]
    private ?int $numero;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['read:competence', 'read:departement', 'read:ressource', 'read:sae'])]
    private ?int $numeroIdentifiant;

    /**
     * @var Collection<int, ApcCompetenceSemestre>
     */
    #[ORM\OneToMany(mappedBy: 'competence', targetEntity: ApcCompetenceSemestre::class)]
    private Collection $apcCompetenceSemestres;

    #[ORM\ManyToOne(inversedBy: 'apcCompetences')]
    private ?Version $version = null;


    public function __construct(Version $version = null)
    {
        $this->setDepartement(null);//todo: remove
        $this->setVersion($version);
        $this->apcComposanteEssentielles = new ArrayCollection();
        $this->apcNiveaux = new ArrayCollection();
        $this->apcRessourceCompetences = new ArrayCollection();
        $this->apcSaeCompetences = new ArrayCollection();
        $this->apcSituationProfessionnelles = new ArrayCollection();
        $this->apcCompetenceSemestres = new ArrayCollection();
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getNomCourt(): ?string
    {
        return $this->nom_court;
    }

    public function setNomCourt(string $nom_court): self
    {
        $this->nom_court = trim($nom_court);

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcComposanteEssentielles(): Collection
    {
        return $this->apcComposanteEssentielles;
    }

    public function addApcComposanteEssentielle(ApcComposanteEssentielle $apcComposanteEssentielle): self
    {
        if (!$this->apcComposanteEssentielles->contains($apcComposanteEssentielle)) {
            $this->apcComposanteEssentielles[] = $apcComposanteEssentielle;
            $apcComposanteEssentielle->setCompetence($this);
        }

        return $this;
    }

    public function removeApcComposanteEssentielle(ApcComposanteEssentielle $apcComposanteEssentielle): self
    {
        if ($this->apcComposanteEssentielles->contains($apcComposanteEssentielle)) {
            $this->apcComposanteEssentielles->removeElement($apcComposanteEssentielle);
            // set the owning side to null (unless already changed)
            if ($apcComposanteEssentielle->getCompetence() === $this) {
                $apcComposanteEssentielle->setCompetence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcNiveaux(): Collection
    {
        return $this->apcNiveaux;
    }

    public function addApcNiveau(ApcNiveau $apcNiveau): self
    {
        if (!$this->apcNiveaux->contains($apcNiveau)) {
            $this->apcNiveaux[] = $apcNiveau;
            $apcNiveau->setCompetence($this);
        }

        return $this;
    }

    public function removeApcNiveau(ApcNiveau $apcNiveau): self
    {
        if ($this->apcNiveaux->contains($apcNiveau)) {
            $this->apcNiveaux->removeElement($apcNiveau);
            // set the owning side to null (unless already changed)
            if ($apcNiveau->getCompetence() === $this) {
                $apcNiveau->setCompetence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcRessourceCompetences(): Collection
    {
        return $this->apcRessourceCompetences;
    }

    public function addApcRessourceCompetence(ApcRessourceCompetence $apcRessourceCompetence): self
    {
        if (!$this->apcRessourceCompetences->contains($apcRessourceCompetence)) {
            $this->apcRessourceCompetences[] = $apcRessourceCompetence;
            $apcRessourceCompetence->setCompetence($this);
        }

        return $this;
    }

    public function removeApcRessourceCompetence(ApcRessourceCompetence $apcRessourceCompetence): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcRessourceCompetences->removeElement($apcRessourceCompetence) && $apcRessourceCompetence->getCompetence() === $this) {
            $apcRessourceCompetence->setCompetence(null);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcSaeCompetences(): Collection
    {
        return $this->apcSaeCompetences;
    }

    public function addApcSaeCompetence(ApcSaeCompetence $apcSaeCompetence): self
    {
        if (!$this->apcSaeCompetences->contains($apcSaeCompetence)) {
            $this->apcSaeCompetences[] = $apcSaeCompetence;
            $apcSaeCompetence->setCompetence($this);
        }

        return $this;
    }

    public function removeApcSaeCompetence(ApcSaeCompetence $apcSaeCompetence): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcSaeCompetences->removeElement($apcSaeCompetence) && $apcSaeCompetence->getCompetence() === $this) {
            $apcSaeCompetence->setCompetence(null);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcSituationProfessionnelles(): Collection
    {
        return $this->apcSituationProfessionnelles;
    }

    public function addApcSituationProfessionnelle(ApcSituationProfessionnelle $apcSituationProfessionnelle): self
    {
        if (!$this->apcSituationProfessionnelles->contains($apcSituationProfessionnelle)) {
            $this->apcSituationProfessionnelles[] = $apcSituationProfessionnelle;
            $apcSituationProfessionnelle->setCompetence($this);
        }

        return $this;
    }

    public function removeApcSituationProfessionnelle(ApcSituationProfessionnelle $apcSituationProfessionnelle): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcSituationProfessionnelles->removeElement($apcSituationProfessionnelle) && $apcSituationProfessionnelle->getCompetence() === $this) {
            $apcSituationProfessionnelle->setCompetence(null);
        }

        return $this;
    }

    /** @deprecated */
    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    /** @deprecated */
    public function setDepartement(?Departement $departement): self
    {
        //todo: a adapter pour passer par Version
        $this->departement = $departement;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getNumeroIdentifiant(): ?int
    {
        return $this->numeroIdentifiant;
    }

    public function setNumeroIdentifiant(int $numeroIdentifiant): self
    {
        $this->numeroIdentifiant = $numeroIdentifiant;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcCompetenceSemestres(): Collection
    {
        return $this->apcCompetenceSemestres;
    }

    public function addApcCompetenceSemestre(ApcCompetenceSemestre $apcCompetenceSemestre): self
    {
        if (!$this->apcCompetenceSemestres->contains($apcCompetenceSemestre)) {
            $this->apcCompetenceSemestres[] = $apcCompetenceSemestre;
            $apcCompetenceSemestre->setCompetence($this);
        }

        return $this;
    }

    public function removeApcCompetenceSemestre(ApcCompetenceSemestre $apcCompetenceSemestre): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcCompetenceSemestres->removeElement($apcCompetenceSemestre) && $apcCompetenceSemestre->getCompetence() === $this) {
            $apcCompetenceSemestre->setCompetence(null);
        }

        return $this;
    }

    public function getCleUnique(): string
    {
        return md5($this->libelle);
    }

    public function isGoodParcours(?ApcParcours $apcParcours = null): bool
    {
        if (!$apcParcours instanceof ApcParcours) {
            return true;
        }

        if ($this->apcNiveaux->count() === 0) {
            //pas de parcours dans la SAE, donc tous les parcours
            return true;
        }

        foreach ($this->apcNiveaux as $apcNiveau) {
            foreach ($apcNiveau->getApcParcoursNiveaux() as $parcours) {
                if ($parcours->getParcours()->getId() === $apcParcours->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function setVersion(?Version $version): static
    {
        $this->version = $version;

        return $this;
    }
}
