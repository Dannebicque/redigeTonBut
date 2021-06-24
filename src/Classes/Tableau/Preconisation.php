<?php


namespace App\Classes\Tableau;

use App\DTO\PreconisationDepartement;
use App\DTO\PreconisationSemestre;

class Preconisation
{
    private array $semestres;
    private array $competences;
    private array $donneesSemestres;

    private PreconisationDepartement $donneesDepartement;

    public function setSemestresCompetences(array $semestres, array $competences)
    {
        $this->semestres = $semestres;
        $this->competences = $competences;
        return $this;
    }

    public function getDataJson()
    {
        $this->donneesSemestres = [];
        $this->donneesDepartement = new PreconisationDepartement();
        $json = [];
        foreach ($this->semestres as $semestre)
        {
            $sem = new PreconisationSemestre($semestre, $this->competences);
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
