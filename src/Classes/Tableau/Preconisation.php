<?php


namespace App\Classes\Tableau;

use App\DTO\PreconisationDepartement;
use App\DTO\PreconisationSemestre;
use App\Entity\ApcParcours;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcParcoursNiveauRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;

class Preconisation
{
    /**
     * @var \App\Repository\ApcSaeParcoursRepository
     */
    public $apcSaeParcoursRepository;
    /**
     * @var \App\Repository\ApcRessourceParcoursRepository
     */
    public $apcRessourceParcoursRepository;
    private array $semestres;
    private array $donneesSemestres;

    private ApcRessourceRepository $apcRessourceRepository;

    private ApcSaeRepository $apcSaeRepository;

    private PreconisationDepartement $donneesDepartement;

    private ?ApcParcours $apcParcours;

    private ApcParcoursNiveauRepository $apcParcoursNiveauRepository;

    private ApcNiveauRepository $apcNiveauRepository;

    public function __construct(
        ApcRessourceRepository $apcRessourceRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcParcoursNiveauRepository $apcParcoursNiveauRepository,
        ApcNiveauRepository $apcNiveauRepository,

    ) {
        $this->apcRessourceRepository = $apcRessourceRepository;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->apcSaeParcoursRepository = $apcSaeParcoursRepository;
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
        $this->apcParcoursNiveauRepository = $apcParcoursNiveauRepository;
        $this->apcNiveauRepository = $apcNiveauRepository;
    }


    public function setSemestresCompetences(array $semestres, ?ApcParcours $apcParcours = null): static
    {
        $this->semestres = $semestres;
        $this->apcParcours = $apcParcours;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getDataJson(): array
    {
        $this->donneesSemestres = [];
        $this->donneesDepartement = new PreconisationDepartement();
        $json = [];
        foreach ($this->semestres as $semestre) {
            if ($this->apcParcours instanceof ApcParcours) {
                $competences = $this->apcParcoursNiveauRepository->findParcoursSemestreCompetence($semestre, $this->apcParcours);
                $ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $this->apcParcours);
                $ressourcesAl = $this->apcRessourceParcoursRepository->findBySemestreAl($semestre, $this->apcParcours);
                $saes = $this->apcSaeParcoursRepository->findBySemestre($semestre, $this->apcParcours);
                $saesAl = $this->apcSaeParcoursRepository->findBySemestreAl($semestre, $this->apcParcours);
            } else {
                $competences = $this->apcNiveauRepository->findBySemestreArrayCompetence($semestre);
                $ressources = $this->apcRessourceRepository->findBySemestre($semestre);
                $ressourcesAl = $this->apcRessourceRepository->findBySemestreAl($semestre);
                $saes = $this->apcSaeRepository->findBySemestre($semestre);
                $saesAl = $this->apcSaeRepository->findBySemestreAl($semestre);
            }

            $sem = new PreconisationSemestre($semestre, $competences, $ressources, $saes, $ressourcesAl, $saesAl, $this->apcParcours);

            $json[$semestre->getOrdreLmd()] = $sem->getJson();
            $this->donneesDepartement->addSemestre($sem);
        }

        return $json;
    }

    public function semestre(int $i): ?PreconisationSemestre
    {
        if (array_key_exists($i, $this->donneesSemestres)) {
            return $this->donneesSemestres[$i];
        }

        return null;
    }
}
