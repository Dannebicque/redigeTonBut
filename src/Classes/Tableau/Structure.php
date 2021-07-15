<?php
namespace App\Classes\Tableau;

use App\Classes\Excel\ExcelWriter;
use App\DTO\StructureDepartement;
use App\DTO\StructureSemestre;
use App\Entity\Departement;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Structure
{
    private array $semestres;
    private array $donneesSemestres;
    private StructureDepartement $donneesDepartement;
    private Departement $departement;

    public function setDepartement(Departement $departement): Structure
    {
        $this->departement = $departement;
        return $this;
    }

    public function setSemestres(array $semestres): Structure
    {
        $this->semestres = $semestres;
        return $this;
    }

    public function getDataTableau(): Structure
    {
        $this->donneesSemestres = [];
        $this->donneesDepartement = new StructureDepartement();
        $this->donneesDepartement->setDepartement($this->departement);

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

    public function getDataDepartement(): StructureDepartement
    {
        return $this->donneesDepartement;
    }

    public function getDataJson(): array
    {
        $json = [];
        $this->donneesDepartement = new StructureDepartement();
        $this->donneesDepartement->setDepartement($this->departement);
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
    ): StreamedResponse {
        $this->departement = $departement;
        $this->semestres = $departement->getSemestres();

        $this->getDataTableau();
        $spreadsheet = $excelWriter->createFromTemplate('tableau_structure.xlsx');

        //complète le fichier
        $sheet = $spreadsheet->getSheetByName('vol_global_T');
        if ($sheet !== null) {
            $sheet->getCell('B4')->setValue('BUT ' . $this->departement->getSigle());
            $sheet->getCell('C4')->setValue($this->departement->getTypeDepartement());

            for ($i = 1; $i <= 6; $i++) {
                $sheet->getCellByColumnAndRow(2 + $i, 7)->setValue($this->donneesSemestres[$i]->nbHeuresRessourcesSae);
                $sheet->getCellByColumnAndRow(2 + $i, 9)->setValue($this->donneesSemestres[$i]->pourcentageAdaptationLocale / 100);
                $sheet->getCellByColumnAndRow(2 + $i, 11)->setValue($this->donneesSemestres[$i]->nbHeuresEnseignementSaeLocale);
                $sheet->getCellByColumnAndRow(2 + $i, 14)->setValue($this->donneesSemestres[$i]->nbHeuresTpNational);
                $sheet->getCellByColumnAndRow(2 + $i, 15)->setValue($this->donneesSemestres[$i]->nbHeuresTpLocale);
                $sheet->getCellByColumnAndRow(2 + $i, 17)->setValue($this->donneesSemestres[$i]->nbHeuresProjet);
                if ($this->donneesSemestres[$i]->nbSemainesStageMin !== $this->donneesSemestres[$i]->nbSemainesStageMax) {
                    $sheet->getCellByColumnAndRow(2 + $i,
                        22)->setValue($this->donneesSemestres[$i]->nbSemainesStageMin . ' - ' . $this->donneesSemestres[$i]->nbSemainesStageMax);
                } else {
                    $sheet->getCellByColumnAndRow(2 + $i,
                        22)->setValue( $this->donneesSemestres[$i]->nbSemainesStageMax);
                }
            }
        }
        $excelWriter->setSpreadsheet($spreadsheet);
        return $excelWriter->genereFichier('structure_'.$departement->getSigle());

    }
}
