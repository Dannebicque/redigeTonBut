<?php
namespace App\Classes\Tableau;

use App\Classes\Excel\ExcelWriter;
use App\DTO\StructureDepartement;
use App\DTO\StructureSemestre;
use App\Entity\ApcRessourceParcours;
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

        $tRessources = [
            'ia' => ['sum' => 0.0, 'weight' => 0.0],
            'teds' => ['sum' => 0.0, 'weight' => 0.0],
            'ppp' => ['sum' => 0.0, 'weight' => 0.0],
            'expression' => ['sum' => 0.0, 'weight' => 0.0],
            'lve' => ['sum' => 0.0, 'weight' => 0.0],
        ];

        /** @var ApcRessource $ressource */
        foreach ($ressources as $ressource) {
            $heures = (float) $ressource->getHeuresTotales();
            $poids = (float) $this->getPoidsParcoursRessource($ressource);

            if ($ressource->isRessourceIA()) {
                $tRessources['ia']['sum'] += $heures * $poids;
                $tRessources['ia']['weight'] += $poids;
            }
            if ($ressource->isRessourceTEDS()) {
                $tRessources['teds']['sum'] += $heures * $poids;
                $tRessources['teds']['weight'] += $poids;
            }
            if ($ressource->isRessourceLve()) {
                $tRessources['lve']['sum'] += $heures * $poids;
                $tRessources['lve']['weight'] += $poids;
            }
            if ($ressource->isRessourceExpression())
            {
                $tRessources['expression']['sum'] += $heures * $poids;
                $tRessources['expression']['weight'] += $poids;
            }

            if ($ressource->isRessourcePpp())
            {
                $tRessources['ppp']['sum'] += $heures * $poids;
                $tRessources['ppp']['weight'] += $poids;
            }

        }

        $ressourcesMoyenne = [];
        foreach ($tRessources as $key => $stats) {
            $ressourcesMoyenne[$key] = $stats['weight'] > 0.0 ? $stats['sum'] / $stats['weight'] : 0.0;
        }


        return [
            'nbCompetences' => $this->apcCompetenceRepository->countBySemestre($semestre),
            'nbRessources' => count( $ressources),
            'nbSaes' => $this->apcSaeRepository->countBySemestre($semestre),
            'nbSaesMonoCompetence' => $this->apcSaeRepository->countBySemestreMonoCompetence($semestre),
            'nbSaesMultiCompetence' => $this->apcSaeRepository->countBySemestreMultiCompetence($semestre),
            'ressources' => $ressourcesMoyenne,
        ];
    }

    private function getPoidsParcoursRessource(ApcRessource $ressource): int
    {
        $totalParcours = $this->version->getApcParcours()->count();

        // Cas limite: si la version n'a pas de parcours, on garde un poids neutre.
        if ($totalParcours === 0) {
            return 1;
        }

        // Pas de restriction explicite: la ressource porte sur tous les parcours.
        if ($ressource->getApcRessourceParcours()->count() === 0) {
            return $totalParcours;
        }

        $parcoursIds = [];
        foreach ($ressource->getApcRessourceParcours() as $ressourceParcours) {
            if (!$ressourceParcours instanceof ApcRessourceParcours) {
                continue;
            }

            $parcours = $ressourceParcours->getParcours();
            if ($parcours instanceof ApcParcours && $parcours->getId() !== null) {
                $parcoursIds[$parcours->getId()] = true;
            }
        }

        $nbParcoursRessource = count($parcoursIds);
        if ($nbParcoursRessource === 0) {
            return $totalParcours;
        }

        return min($nbParcoursRessource, $totalParcours);
    }
}
