<?php
namespace App\Classes\Tableau;

use App\Classes\Excel\ExcelWriter;
use App\DTO\StructureDepartement;
use App\DTO\StructureSemestre;
use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Entity\Semestre;
use App\Entity\Version;
use App\Repository\ApcComptenceRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Structure
{
    private array $semestres;

    private array $donneesSemestres;

    private StructureDepartement $donneesDepartement;

    private Version $version;

    public function __construct(
        private ApcComptenceRepository $apcCompetenceRepository,
        private ApcRessourceRepository $apcRessourceRepository,
        private ApcSaeRepository $apcSaeRepository,
        )
    {}

    public function setVersion(Version $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function setSemestres(array $semestres): self
    {
        $this->semestres = $semestres;
        return $this;
    }

    public function getDataTableau(): self
    {
        $this->donneesSemestres = [];
        $this->donneesDepartement = new StructureDepartement();
        $this->donneesDepartement->setVersion($this->version);

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
        $this->donneesDepartement->setVersion($this->version);
        foreach ($this->semestres as $semestre)
        {
            $sem = new StructureSemestre($semestre);
            $json[$semestre->getOrdreLmd()] = $sem->getJson();
            $json[$semestre->getOrdreLmd()]['structureSemestre'] = $this->getStructureSemestre($semestre);
            $this->donneesDepartement->addSemestre($sem);
        }

        $json['version'] = $this->donneesDepartement->getJson();

        return $json;
    }

    public function genereFichierExcel(
        ExcelWriter $excelWriter,
        Version $version,
        ?ApcParcours $parcours = null
    ): StreamedResponse {
        $this->version = $version;
        $this->semestres = $parcours instanceof ApcParcours ? $parcours->getSemestresArray() : $version->getSemestres();
        $departement = $version->getDepartement();
        $this->getDataTableau();
        $spreadsheet = $excelWriter->createFromTemplate('tableau_structure.xlsx');

        //complète le fichier
        $sheet = $spreadsheet->getSheetByName('vol_global_T');
        if ($sheet !== null) {
            $sheet->getCell('B4')->setValue('BUT ' . $departement->getSigle());
            $sheet->getCell('C4')->setValue($departement->getTypeDepartement());
            if ($parcours instanceof ApcParcours) {
                $sheet->getCell('B5')->setValue('PARCOURS '.$parcours->getLibelle());
            }

            for ($i = 1; $i <= 6; $i++) {
                $sheet->getCellByColumnAndRow(2 + $i, 7)->setValue($this->donneesSemestres[$i]->nbHeuresRessourcesSae);
                $sheet->getCellByColumnAndRow(2 + $i, 9)->setValue($this->donneesSemestres[$i]->pourcentageAdaptationLocale / 100);
                $sheet->getCellByColumnAndRow(2 + $i, 10)->setValue($this->donneesSemestres[$i]->nbHeuresEnseignementLocale);
                $sheet->getCellByColumnAndRow(2 + $i, 11)->setValue($this->donneesSemestres[$i]->nbHeuresEnseignementSaeLocale);
                $sheet->getCellByColumnAndRow(2 + $i, 12)->setValue($this->donneesSemestres[$i]->nbHeuresEnseignementRessourceLocale);
                $sheet->getCellByColumnAndRow(2 + $i, 13)->setValue($this->donneesSemestres[$i]->nbHeuresEnseignementRessourceNational);
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

    private function getStructureSemestre(Semestre $semestre): array
    {
        $ressources = $this->apcRessourceRepository->findBySemestre($semestre);

        $tRessources['ia'] = 0;
        $tRessources['teds'] = 0;
        $tRessources['ppp'] = 0;
        $tRessources['expression'] = 0;
        $tRessources['lve'] = 0;
        $tRessources['ppp'] = 0;

        /** @var ApcRessource $ressource */
        foreach ($ressources as $ressource) {
            if ($ressource->isRessourceIA()) {
                $tRessources['ia'] += $ressource->getHeuresTotales();
            }
            if ($ressource->isRessourceTEDS()) {
                $tRessources['teds'] += $ressource->getHeuresTotales();
            }
            if ($ressource->isRessourceLve()) {
                $tRessources['lve'] += $ressource->getHeuresTotales();
            }
            if ($ressource->isRessourceExpression())
            {
                $tRessources['expression'] += $ressource->getHeuresTotales();
            }

            if ($ressource->isRessourcePpp())
            {
                $tRessources['ppp'] += $ressource->getHeuresTotales();
            }

        }


        return [
            'nbCompetences' => $this->apcCompetenceRepository->countBySemestre($semestre),
            'nbRessources' => count( $ressources),
            'nbSaes' => $this->apcSaeRepository->countBySemestre($semestre),
            'nbSaesMonoCompetence' => $this->apcSaeRepository->countBySemestreMonoCompetence($semestre),
            'nbSaesMultiCompetence' => $this->apcSaeRepository->countBySemestreMultiCompetence($semestre),
            'ressources' => $tRessources,
        ];
    }
}
