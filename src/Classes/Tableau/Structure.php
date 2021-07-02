<?php
namespace App\Classes\Tableau;

use App\Classes\Excel\ExcelWriter;
use App\DTO\StructureDepartement;
use App\DTO\StructureSemestre;
use App\Entity\Departement;

class Structure
{

    private array $semestres;
    private array $donneesSemestres;
    private StructureDepartement $donneesDepartement;

    public function setSemestres(array $semestres)
    {
        $this->semestres = $semestres;
        return $this;
    }

    public function getDataTableau()
    {
        $this->donneesSemestres = [];
        $this->donneesDepartement = new StructureDepartement();
        foreach ($this->semestres as $semestre)
        {
            $this->donneesSemestres[$semestre->getOrdreLmd()] = new StructureSemestre($semestre);
            $this->donneesDepartement->addSemestre($this->donneesSemestres[$semestre->getOrdreLmd()]);
        }

        return $this;
    }

    public function semestre(int $i): ?StructureSemestre
    {
        if (array_key_exists($i, $this->donneesSemestres)) {
            return $this->donneesSemestres[$i];
        }
        return null;
    }

    public function getDataDepartement()
    {
        return $this->donneesDepartement;
    }

    public function getDataJson()
    {
        $json = [];
        $this->donneesDepartement = new StructureDepartement();
        foreach ($this->semestres as $semestre)
        {
            $sem = new StructureSemestre($semestre);
            $json[$semestre->getOrdreLmd()] = $sem->getJson();
            $this->donneesDepartement->addSemestre($sem);
        }
        $json['departement'] = $this->donneesDepartement->getJson();

        return $json;
    }

    public function genereFichierExcel(
        ExcelWriter $excelWriter,
        Departement $departement
    ) {
        $excelWriter->nouveauFichier('vol_global'); //todo: pourrait se baser sur un modèle ?
        //todo: si plusieurs parcours, plusieurs fichiers ?

        return $excelWriter->genereFichier('structure_'.$departement->getSigle());

    }
}
