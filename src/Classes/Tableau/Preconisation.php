<?php


namespace App\Classes\Tableau;

use App\DTO\PreconisationDepartement;
use App\DTO\PreconisationSemestre;
use App\Entity\ApcParcours;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcParcoursNiveauRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;

class Preconisation
{
    private array $semestres;
    private array $competences;
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
        ApcParcoursNiveauRepository $apcParcoursNiveauRepository,
        ApcNiveauRepository $apcNiveauRepository,

    ) {
        $this->apcRessourceRepository = $apcRessourceRepository;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->apcParcoursNiveauRepository = $apcParcoursNiveauRepository;
        $this->apcNiveauRepository = $apcNiveauRepository;
    }


    public function setSemestresCompetences(array $semestres, ?ApcParcours $apcParcours = null)
    {
        $this->semestres = $semestres;
        $this->apcParcours = $apcParcours;


        return $this;
    }

    public function getDataJson()
    {
        $this->donneesSemestres = [];
        $this->donneesDepartement = new PreconisationDepartement();
        $json = [];
        foreach ($this->semestres as $semestre) {
            $ressources = $this->apcRessourceRepository->findBySemestreAndParcours($semestre, $this->apcParcours);
            $saes = $this->apcSaeRepository->findBySemestreAndParcours($semestre, $this->apcParcours);
            if ($this->apcParcours !== null) {
                $competences = $this->apcParcoursNiveauRepository->findParcoursSemestreCompetence($semestre, $this->apcParcours);
            } else {
                $competences = $this->apcNiveauRepository->findBySemestreArrayCompetence($semestre);
            }

            $sem = new PreconisationSemestre($semestre, $competences, $ressources, $saes);
            $json[$semestre->getOrdreLmd()] = $sem->getJson();
            $this->donneesDepartement->addSemestre($sem);
        }

        // $json['departement'] = $this->donneesDepartement->getJson();

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
