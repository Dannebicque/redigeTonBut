<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Entity/ApcSae.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 12/05/2021 15:23
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\LifeCycleTrait;
use App\Repository\ApcSaeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\AbstractUnicodeString;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Controller\api\GetSaesSpecialite;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read:sae"}},
 *     collectionOperations={
 *     "get",
 *     "get_by_specialite"={
 *         "method"="GET",
 *         "path"="/specialite/{specialite}/saes",
 *     "defaults"={"annee"=2022},
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
 *          {
 *  "name" = "annee",
 *  "in" = "query",
 *  "description" = "Année",
 *  "required" = false,
 *  "schema"={
 *  "type" : "integer",
 *  "default" : 2022
 *  }
 *  }
 *           }
 *     },
 *         "controller"=GetSaesSpecialite::class,
 *     }},
 *     itemOperations={"get"}
 * )
 */
#[ORM\Entity(repositoryClass: ApcSaeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ApcSae extends AbstractMatiere
{
    use LifeCycleTrait;

    public const string SOURCE = 'sae';

    #[ORM\ManyToOne(targetEntity: Semestre::class, inversedBy: 'apcSaes')]
    #[Groups(['read:sae'])]
    private ?Semestre $semestre;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(['read:sae'])]
    private float $projetPpn = 0;

    /**
     * @var Collection<int, ApcSaeCompetence>
     */
    #[ORM\OneToMany(mappedBy: 'sae', targetEntity: ApcSaeCompetence::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:sae'])]
    private Collection $apcSaeCompetences;

    /**
     * @var Collection<int, ApcSaeRessource>
     */
    #[ORM\OneToMany(mappedBy: 'sae', targetEntity: ApcSaeRessource::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:sae'])]
    private Collection $apcSaeRessources;

    /**
     * @var Collection<int, ApcSaeApprentissageCritique>
     */
    #[ORM\OneToMany(mappedBy: 'sae', targetEntity: ApcSaeApprentissageCritique::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:sae'])]
    private Collection $apcSaeApprentissageCritiques;

    /**
     * @var Collection<int, ApcSaeParcours>
     */
    #[ORM\OneToMany(mappedBy: 'sae', targetEntity: ApcSaeParcours::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[Groups(['read:sae'])]
    private Collection $apcSaeParcours;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['read:sae'])]
    private ?int $ordre;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['read:sae'])]
    private ?string $objectifs;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['read:sae'])]
    private ?string $exemples;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $ficheAdaptationLocale = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['read:sae'])]
    private ?bool $portfolio = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['read:sae'])]
    private ?bool $stage = false;

    /**
     * @var Collection<int, QapesSae>
     */
    #[ORM\OneToMany(mappedBy: 'sae', targetEntity: QapesSae::class)]
    private Collection $qapesSaes;

    public function __construct()
    {
        $this->apcSaeCompetences = new ArrayCollection();
        $this->apcSaeRessources = new ArrayCollection();
        $this->apcSaeApprentissageCritiques = new ArrayCollection();
        $this->apcSaeParcours = new ArrayCollection();
        $this->qapesSaes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getLibelle();
    }

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

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
            $apcSaeCompetence->setSae($this);
        }

        return $this;
    }

    public function removeApcSaeCompetence(ApcSaeCompetence $apcSaeCompetence): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcSaeCompetences->removeElement($apcSaeCompetence) && $apcSaeCompetence->getSae() === $this) {
            $apcSaeCompetence->setSae(null);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcSaeRessources(): Collection
    {
        return $this->apcSaeRessources;
    }

    public function addApcSaeRessource(ApcSaeRessource $apcSaeRessource): self
    {
        if (!$this->apcSaeRessources->contains($apcSaeRessource)) {
            $this->apcSaeRessources[] = $apcSaeRessource;
            $apcSaeRessource->setSae($this);
        }

        return $this;
    }

    public function removeApcSaeRessource(ApcSaeRessource $apcSaeRessource): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcSaeRessources->removeElement($apcSaeRessource) && $apcSaeRessource->getSae() === $this) {
            $apcSaeRessource->setSae(null);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCompetences(): Collection
    {
        $comptences = new ArrayCollection();

        foreach ($this->getApcSaeCompetences() as $apcSaeCompetence) {
            $comptences->add($apcSaeCompetence->getCompetence());
        }

        return $comptences;
    }

    /**
     * @return $this
     */
    public function addCompetence(ApcCompetence $competence): self
    {
        $apcSaeCompetence = new ApcSaeCompetence($this, $competence);
        $this->addApcSaeCompetence($apcSaeCompetence);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeCompetence(ApcCompetence $competence): self
    {
        foreach ($this->apcSaeCompetences as $apcSaeCompetence) {
            if ($apcSaeCompetence->getCompetence() === $competence) {
                $this->apcSaeCompetences->removeElement($apcSaeCompetence);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcSaeApprentissageCritiques(): Collection
    {
        return $this->apcSaeApprentissageCritiques;
    }

    public function addApcSaeApprentissageCritique(ApcSaeApprentissageCritique $apcSaeApprentissageCritique): self
    {
        if (!$this->apcSaeApprentissageCritiques->contains($apcSaeApprentissageCritique)) {
            $this->apcSaeApprentissageCritiques[] = $apcSaeApprentissageCritique;
            $apcSaeApprentissageCritique->setSae($this);
        }

        return $this;
    }

    public function removeApcSaeApprentissageCritique(ApcSaeApprentissageCritique $apcSaeApprentissageCritique): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcSaeApprentissageCritiques->removeElement($apcSaeApprentissageCritique) && $apcSaeApprentissageCritique->getSae() === $this) {
            $apcSaeApprentissageCritique->setSae(null);
        }

        return $this;
    }

    public function getProjetPpn(): ?float
    {
        return $this->projetPpn;
    }

    public function setProjetPpn(float $projetPpn): self
    {
        $this->projetPpn = $projetPpn;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getApcSaeParcours(): Collection
    {
        return $this->apcSaeParcours;
    }

    public function addApcSaeParcour(ApcSaeParcours $apcSaeParcour): self
    {
        if (!$this->apcSaeParcours->contains($apcSaeParcour)) {
            $this->apcSaeParcours[] = $apcSaeParcour;
            $apcSaeParcour->setSae($this);
        }

        return $this;
    }

    public function removeApcSaeParcour(ApcSaeParcours $apcSaeParcour): self
    {
        // set the owning side to null (unless already changed)
        if ($this->apcSaeParcours->removeElement($apcSaeParcour) && $apcSaeParcour->getSae() === $this) {
            $apcSaeParcour->setSae(null);
        }

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getVersion():?Version
    {
        return $this->getSemestre()?->getAnnee()?->getVersion();
    }

    public function getObjectifs(): ?string
    {
        return $this->objectifs;
    }

    public function setObjectifs(string $objectifs): self
    {
        $this->objectifs = $objectifs;

        return $this;
    }

    public function getExemples(): ?string
    {
        return $this->exemples;
    }

    public function setExemples(?string $exemples): self
    {
        $this->exemples = $exemples;

        return $this;
    }

    public function getFicheAdaptationLocale(): ?bool
    {
        return $this->ficheAdaptationLocale;
    }

    public function setFicheAdaptationLocale(?bool $ficheAdaptationLocale): self
    {
        $this->ficheAdaptationLocale = $ficheAdaptationLocale;

        return $this;
    }

    public function getPortfolio(): ?bool
    {
        return $this->portfolio;
    }

    public function setPortfolio(bool $portfolio): self
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    public function getStage(): ?bool
    {
        return $this->stage;
    }

    public function setStage(bool $stage): self
    {
        $this->stage = $stage;

        return $this;
    }

    public function getSlugName(): AbstractUnicodeString
    {
        $slugger = new AsciiSlugger();
        return $slugger->slug($this->getCodeMatiere());
    }

    public function isGoodParcours(?ApcParcours $apcParcours = null): bool
    {
        if (!$apcParcours instanceof ApcParcours) {
            return true;
        }

        if ($this->apcSaeParcours->count() === 0) {
            //pas de parcours dans la SAE, donc tous les parcours
            return true;
        }

        foreach ($this->apcSaeParcours as $apcSaeParcour) {
            if ($apcSaeParcour->getParcours()->getId() === $apcParcours->getId()) {
                return true;
            }
        }

        return false;
    }

    public function apcSaeRessourcesOrdre(?ApcParcours $apcParcours = null): Collection | array
    {
        $ressources = $this->apcSaeRessources;
        $t = [];
        foreach ($ressources as $ressource)
        {
            if ($ressource->getRessource()?->isGoodParcours($apcParcours)) {

                $t[$ressource->getRessource()->getOrdre()] = $ressource->getRessource();
            }
        }

        ksort($t);
        return $t;
    }

    public function apcSaeApprentissageCritiquesOrdre(?ApcParcours $apcParcours = null): Collection | array
    {
        $acs = $this->apcSaeApprentissageCritiques;
        $t = [];
        foreach ($acs as $ac)
        {
            if ($ac->getApprentissageCritique()->getCompetence()?->isGoodParcours($apcParcours)) {
                if (!array_key_exists($ac->getApprentissageCritique()->getCompetence()->getCouleur(), $t)) {
                    $t[$ac->getApprentissageCritique()->getCompetence()->getCouleur()] = [];
                }

                $t[$ac->getApprentissageCritique()->getCompetence()->getCouleur()][$ac->getApprentissageCritique()->getOrdre()] = $ac->getApprentissageCritique();
            }
        }

        ksort($t);
        foreach (array_keys($t) as $couleur) {
            ksort($t[$couleur]);
        }

        return $t;
    }

    /**
     * @return Collection<int, QapesSae>
     */
    public function getQapesSaes(): Collection
    {
        return $this->qapesSaes;
    }

    public function addQapesSae(QapesSae $qapesSae): self
    {
        if (!$this->qapesSaes->contains($qapesSae)) {
            $this->qapesSaes[] = $qapesSae;
            $qapesSae->setSae($this);
        }

        return $this;
    }

    public function removeQapesSae(QapesSae $qapesSae): self
    {
        // set the owning side to null (unless already changed)
        if ($this->qapesSaes->removeElement($qapesSae) && $qapesSae->getSae() === $this) {
            $qapesSae->setSae(null);
        }

        return $this;
    }

    public function getCleUnique(): string
    {
        // Clé métier stable entre années.
        // Objectif : reconnaître “la même SAE” même si des relations annexes (parcours, AC)
        // ou des champs de contenu (objectifs/exemples) évoluent d’une année à l’autre.
        // Donc : on base la clé sur les attributs structurels + références stables.

        $normalizeString = static function (?string $value): string {
            $value = $value ?? '';
            $value = trim($value);
            $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
            return mb_strtolower($value);
        };

        $payload = [
            'code' => $normalizeString($this->getCodeMatiere()),
            'libelle' => $normalizeString($this->getLibelle()),
            'libelle_court' => $normalizeString($this->getLibelleCourt()),
            'ordre' => $this->getOrdre() ?? 0,
            'portfolio' => $this->getPortfolio() ? 1 : 0,
            'stage' => $this->getStage() ? 1 : 0,
            'description' => $normalizeString($this->getDescription()),
            'heures_totales' => (float)($this->getHeuresTotales() ?? 0),
        ];

        return md5(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
