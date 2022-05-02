<?php

namespace App\Classes;

use App\Entity\Departement;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\Wizard;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\Style;

class Mcc
{
    private array $tabRefTotalParcours = [];

    public function __construct(
        private ApcNiveauRepository $apcNiveauRepository,
        private ApcRessourceRepository $apcRessourceRepository,
        private ApcSaeRepository $apcSaeRepository
    ) {
    }

    public function genereFichierExcel(
        Excel\ExcelWriter $excelWriter,
        Departement $departement,
        string $iut = 'troyes',
        array $parcours = [],
        bool $fi = true
    ) {
        $spreadsheet = $excelWriter->createFromTemplate('mcc_' . $iut . '.xlsx');
        $semestres = $departement->getSemestres();

        $tabRefParcours = [];
        $this->tabRefTotalParcours = [];
        $tabRefTotalParcours = [];
        $tabRefCompetences = [];


        // Prépare le modèle avant de dupliquer
        $sheetModele = $spreadsheet->getSheetByName('modele');
        $sheetModele1 = $spreadsheet->getSheetByName('modele_1'); //modèle du BUT 1

        if ($sheetModele !== null && $sheetModele1) {

            if ($departement->getTypeDepartement() === Departement::SECONDAIRE) {
                $sheetModele->setCellValue('M1', 'Sciences, Technologies, Santé');
                $sheetModele1->setCellValue('M1', 'Sciences, Technologies, Santé');
            } else {
                $sheetModele->setCellValue('M1', 'Droit, Economie, Gestion, Sciences sociale');
                $sheetModele1->setCellValue('M1', 'Droit, Economie, Gestion, Sciences sociale');
            }
            $sheetModele->setCellValue('M4', $departement->getLibelle());
            $sheetModele1->setCellValue('M4', $departement->getLibelle());

            if ($fi === false) {
                $sheetModele->setCellValue('E8', '');
                $sheetModele->setCellValue('E10', 'X');
                $sheetModele->setCellValue('E12', 'X');
                $sheetModele->setCellValue('E14', 'X');

                $sheetModele1->setCellValue('E8', '');
                $sheetModele1->setCellValue('E10', 'X');
                $sheetModele1->setCellValue('E12', 'X');
                $sheetModele1->setCellValue('E14', 'X');
            }

            //création des parcours, uniquement sur modèle
            $i = 0;
            $col = Coordinate::columnIndexFromString('AO');
            $rowTotal = 51;

            foreach ($parcours as $parc) {

                if ($i >= 1) {
                    //dupliquer une colonne
                    $sheetModele->insertNewColumnBefore(Coordinate::stringFromColumnIndex($col + $i));
                    for ($ligne = 17; $ligne <= 50; $ligne++) {
                        $cellBase = Coordinate::stringFromColumnIndex($col) . $ligne;
                        $cellCopy = Coordinate::stringFromColumnIndex($col + $i) . $ligne;
                        $sheetModele->duplicateStyle($sheetModele->getStyle($cellBase), $cellCopy);
                    }

                    //Ajouter bloc total en bas de tableau
                    $sheetModele->insertNewRowBefore($rowTotal, 5);
                    $ligneBase = 51;
                    for ($ligne = $rowTotal; $ligne <= $rowTotal + 4; $ligne++) {

                        for ($colCopy = 1; $colCopy < 50; $colCopy++) {
                            $cellBase = Coordinate::stringFromColumnIndex($colCopy) . $ligneBase;
                            $cellCopy = Coordinate::stringFromColumnIndex($colCopy) . $ligne;
                            $sheetModele->setCellValue($cellCopy, $sheetModele->getCell($cellBase)->getValue());
                            $styleArray = $sheetModele->getStyle($cellBase)->exportArray();
                            $sheetModele->getStyle($cellCopy)->applyFromArray($styleArray);
                            $sheetModele->duplicateStyle($sheetModele->getStyle($cellBase), $cellCopy);
                        }
                        $ligneBase++;
                    }


                }
                $tabRefParcours[$parc->getId()] = $col + $i;

                // partie heures
                $sheetModele->mergeCellsByColumnAndRow(Coordinate::columnIndexFromString('A'), $rowTotal,
                    Coordinate::columnIndexFromString('B'), $rowTotal + 3);
                $sheetModele->getStyle('A' . $rowTotal)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheetModele->getStyle('A' . $rowTotal)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheetModele->mergeCellsByColumnAndRow(Coordinate::columnIndexFromString('C'), $rowTotal,
                    Coordinate::columnIndexFromString('D'), $rowTotal + 1);
                $sheetModele->getStyle('C' . $rowTotal)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheetModele->getStyle('C' . $rowTotal)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheetModele->mergeCellsByColumnAndRow(Coordinate::columnIndexFromString('C'), $rowTotal + 2,
                    Coordinate::columnIndexFromString('D'), $rowTotal + 3);

                $sheetModele->mergeCellsByColumnAndRow(Coordinate::columnIndexFromString('G'), $rowTotal + 1,
                    Coordinate::columnIndexFromString('J'), $rowTotal + 1);
                $sheetModele->getStyle('G' . $rowTotal + 1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // formule de calcul des heures
                $colParc = Coordinate::stringFromColumnIndex($tabRefParcours[$parc->getId()]);
                $locale = 'fr';
                $validLocale = Settings::setLocale($locale);
                if (!$validLocale) {
                    echo 'Unable to set locale to ' . $locale . " - reverting to en_us<br />\n";
                }

                $sheetModele->setCellValue('G' . $rowTotal,
                    '=SUMIF($' . $colParc . '22:$' . $colParc . '49,"X",G22:G49)');
                $sheetModele->setCellValue('G' . $rowTotal + 1,
                    '=H' . $rowTotal . '+I' . $rowTotal . '+J' . $rowTotal . '+G' . $rowTotal);
                $sheetModele->setCellValue('H' . $rowTotal,
                    '=SUMIF($' . $colParc . '22:$' . $colParc . '49,"X",H22:H49)');
                $sheetModele->setCellValue('I' . $rowTotal,
                    '=SUMIF($' . $colParc . '22:$' . $colParc . '49,"X",I22:I49)');
                $sheetModele->setCellValue('J' . $rowTotal,
                    '=SUMIF($' . $colParc . '22:$' . $colParc . '49,"X",J22:J49)');


                // partie total Coefficients
                $sheetModele->mergeCellsByColumnAndRow(Coordinate::columnIndexFromString('AH'), $rowTotal,
                    Coordinate::columnIndexFromString('AN'), $rowTotal + 3);
                $sheetModele->getStyle('AH' . $rowTotal)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheetModele->getStyle('AH' . $rowTotal)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheetModele->mergeCellsByColumnAndRow(Coordinate::columnIndexFromString('AO'), $rowTotal,
                    $col + $i + 1, $rowTotal);
                $sheetModele->getStyle('AO' . $rowTotal)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheetModele->mergeCellsByColumnAndRow(Coordinate::columnIndexFromString('AO'), $rowTotal + 1,
                    $col + $i + 1, $rowTotal + 1);
                $sheetModele->getStyle('AO' . ($rowTotal + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheetModele->mergeCellsByColumnAndRow(Coordinate::columnIndexFromString('AO'), $rowTotal + 2,
                    $col + $i + 1, $rowTotal + 3);
                $sheetModele->getStyle('AO' . ($rowTotal + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheetModele->getRowDimension($rowTotal)->setRowHeight(17.25);
                $sheetModele->getRowDimension($rowTotal + 1)->setRowHeight(17.25);
                $sheetModele->getRowDimension($rowTotal + 2)->setRowHeight(17.25);
                $sheetModele->getRowDimension($rowTotal + 3)->setRowHeight(17.25);

                $sheetModele->mergeCellsByColumnAndRow($col + $i, 18, $col + $i, 21);


                $sheetModele->setCellValueByColumnAndRow($col + $i, 17, $parc->getCode());
                $sheetModele->setCellValueByColumnAndRow($col + $i, 18, $parc->getLibelle());

                $cell1 = Coordinate::stringFromColumnIndex($col + $i) . '18';

                $sheetModele->getStyle($cell1)->getAlignment()->setTextRotation(90);
                $sheetModele->getStyle($cell1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheetModele->setCellValueByColumnAndRow(Coordinate::columnIndexFromString('A'), $rowTotal,
                    'Parcours : ' . $parc->getLibelle());
                $sheetModele->getCellByColumnAndRow(Coordinate::columnIndexFromString('A'),
                    $rowTotal)->getStyle()->getAlignment()->setWrapText(true);

                $sheetModele->setCellValueByColumnAndRow(Coordinate::columnIndexFromString('AH'), $rowTotal,
                    'Parcours : ' . $parc->getLibelle());
                $sheetModele->getCellByColumnAndRow(Coordinate::columnIndexFromString('AH'),
                    $rowTotal)->getStyle()->getAlignment()->setWrapText(true);

                $tabRefTotalParcours[$parc->getId()] = $rowTotal;
                $rowTotal += 5;
                $i++;
            }
            $sheetModele->mergeCellsByColumnAndRow($col, 16, $col + $i - 1, 16);
            $sheetModele->getStyle(Coordinate::StringFromColumnIndex($col) . '16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $cellDebut = Coordinate::StringFromColumnIndex($col) . '16';
            $cellFin = Coordinate::StringFromColumnIndex($col + $i - 1) . '16';
            $sheetModele = $this->borderInsideOutside($cellDebut, $cellFin, $sheetModele);

            $cellDebut = Coordinate::StringFromColumnIndex($col) . '17';
            $cellFin = Coordinate::StringFromColumnIndex($col + $i - 1) . '21';
            $sheetModele = $this->borderInsideOutside($cellDebut, $cellFin, $sheetModele);

            $cellDebut = Coordinate::StringFromColumnIndex($col) . '22';
            $cellFin = Coordinate::StringFromColumnIndex($col + $i - 1) . '49';
            $sheetModele = $this->borderInsideOutside($cellDebut, $cellFin, $sheetModele);

            $debutCompetencesBUT23 = $col + $i + 1;
        }

        /** @var \App\Entity\Semestre $semestre */
        foreach ($semestres as $semestre) {
            $this->tabRefTotalParcours = $tabRefTotalParcours;
            if ($semestre->getOrdreLmd() < 3 && $departement->getTypeStructure() !== Departement::TYPE3) {
                $sheetModele = $spreadsheet->getSheetByName('modele_1');
                $debutCompetences = Coordinate::columnIndexFromString('AO');
            } else {
                $sheetModele = $spreadsheet->getSheetByName('modele');
                $debutCompetences = $debutCompetencesBUT23;
            }

            if ($sheetModele !== null) {
                $sheet = $sheetModele->copy();

                $sheet->setTitle('Semestre ' . $semestre->getOrdreLmd());
                $spreadsheet->addSheet($sheet);
                unset($sheet);
                $spreadsheet->setActiveSheetIndexByName('Semestre ' . $semestre->getOrdreLmd());
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('M6', $semestre->getOrdreLmd());

                //Ajout des colonnes de compétences
                $competences = $this->apcNiveauRepository->findBySemestre($semestre);

                $i = 0;
                foreach ($competences as $competence) {
                    if ($i >= 1) {
                        //dupliquer une colonne
                        $sheet->insertNewColumnBefore(Coordinate::stringFromColumnIndex($debutCompetences + $i));
                        for ($ligne = 17; $ligne <= $rowTotal; $ligne++) {
                            $cellBase = Coordinate::stringFromColumnIndex($debutCompetences) . $ligne;
                            $cellCopy = Coordinate::stringFromColumnIndex($debutCompetences + $i) . $ligne;
                            $sheet->setCellValue($cellCopy, $sheet->getCell($cellBase)->getValue());
                            $sheet->duplicateStyle($sheet->getStyle($cellBase), $cellCopy);
                        }
                    }
                    $sheet->setCellValueByColumnAndRow($debutCompetences + $i, 17, 'BC' . ($i + 1));
                    $sheet->setCellValueByColumnAndRow($debutCompetences + $i, 18,
                        $competence->getCompetence()->getNomCourt());
                    $tabRefCompetences[$competence->getCompetence()->getId()] = $debutCompetences + $i;
                    $cell1 = Coordinate::stringFromColumnIndex($debutCompetences + $i) . '18';
                    $sheet->mergeCellsByColumnAndRow($debutCompetences + $i, 18, $debutCompetences + $i, 21);
                    $sheet->getStyle($cell1)->getAlignment()->setTextRotation(90);
                    $sheet->getStyle($cell1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->setCellValueByColumnAndRow($debutCompetences + $i, 21, 0); //ECTS selon le semestre
                    //formule

                    $i++;
                }
                $sheet->mergeCellsByColumnAndRow($debutCompetences, 16, $debutCompetences + $i - 1, 16);
                $cellDebut = Coordinate::StringFromColumnIndex($debutCompetences) . '16';
                $cellFin = Coordinate::StringFromColumnIndex($debutCompetences + $i - 1) . '16';
                $sheet = $this->borderInsideOutside($cellDebut, $cellFin, $sheet);

                $sheet->getStyle(Coordinate::StringFromColumnIndex($debutCompetences) . '16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $cellDebut = Coordinate::StringFromColumnIndex($debutCompetences) . '17';
                $cellFin = Coordinate::StringFromColumnIndex($debutCompetences + $i - 1) . '21';
                $sheet = $this->borderInsideOutside($cellDebut, $cellFin, $sheet);

                $cellDebut = Coordinate::StringFromColumnIndex($debutCompetences) . '22';
                $cellFin = Coordinate::StringFromColumnIndex($debutCompetences + $i - 1) . '49';
                $sheet = $this->borderInsideOutside($cellDebut, $cellFin, $sheet);


                //Ajout des ressources
                $ressources = $this->apcRessourceRepository->findBySemestre($semestre);
                $ligne = 22;
                foreach ($ressources as $ressource) {
                    $sheet = $this->addLigneIfNececessary($sheet, $ligne);
                    $sheet->setCellValueByColumnAndRow(2, $ligne, $ressource->getCodeMatiere());
                    $sheet->setCellValueByColumnAndRow(3, $ligne, $ressource->getLibelle());
                    $sheet->setCellValueByColumnAndRow(4, $ligne, $ressource->getLibelleCourt());
                    $sheet->setCellValueByColumnAndRow(5, $ligne, $ressource->getHeuresTotales());
                    $sheet->getStyle('E' . $ligne)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

                    $sheet->setCellValue('F' . $ligne,
                        '=SUM(G' . $ligne . ':I' . $ligne . ')');
                    $sheet->getStyle('F' . $ligne)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

                    $cellPtut = Coordinate::stringFromColumnIndex(10) . $ligne;
                    $sheet->getStyle($cellPtut)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFCCCCCC');
                    $cellDebut = Coordinate::stringFromColumnIndex(26) . $ligne;
                    $cellFin = Coordinate::stringFromColumnIndex(39) . $ligne;
                    $sheet->getStyle($cellDebut . ':' . $cellFin)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFCCCCCC');
                    //ajouter croix et griser coefficients ?
                    foreach ($ressource->getApcRessourceCompetences() as $competence) {
                        $sheet->getStyle(Coordinate::stringFromColumnIndex($tabRefCompetences[$competence->getCompetence()->getId()]) . $ligne)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF6D6B8');
                        if ($iut === 'rcc') {
                            $sheet->setCellValueByColumnAndRow($tabRefCompetences[$competence->getCompetence()->getId()],
                                $ligne, $competence->getCoefficient());
                        }
                    }

                    if ($departement->getTypeStructure() === Departement::TYPE3 || $semestre->getOrdreLmd() >= 3) {
                        foreach ($ressource->getApcRessourceParcours() as $apcSaeParcour) {
                            if (array_key_exists($apcSaeParcour->getParcours()->getId(), $parcours)) {

                                $sheet->setCellValueByColumnAndRow($tabRefParcours[$apcSaeParcour->getParcours()->getId()],
                                    $ligne, 'X');
                            }
                        }
                    }
                    $ligne++;
                }
                $ligne++;

                //Ajout des SAE
                $debutSae = $ligne;
                $saes = $this->apcSaeRepository->findBySemestre($semestre);
                foreach ($saes as $sae) {
                    $sheet = $this->addLigneIfNececessary($sheet, $ligne);
                    $sheet->setCellValueByColumnAndRow(2, $ligne, $sae->getCodeMatiere());
                    $sheet->setCellValueByColumnAndRow(3, $ligne, $sae->getLibelle());
                    $sheet->setCellValueByColumnAndRow(4, $ligne, $sae->getLibelleCourt());
                    $sheet->setCellValue('F' . $ligne,
                        '=SUM(G' . $ligne . ':J' . $ligne . ')');
                    $sheet->getStyle('F' . $ligne)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                    $cellDebut = Coordinate::stringFromColumnIndex(14) . $ligne;
                    $cellFin = Coordinate::stringFromColumnIndex(25) . $ligne;
                    $sheet->getStyle($cellDebut . ':' . $cellFin)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFCCCCCC');
                    //ajouter croix et griser coefficients ?
                    foreach ($sae->getApcSaeCompetences() as $competence) {
                        $sheet->getStyle(Coordinate::stringFromColumnIndex($tabRefCompetences[$competence->getCompetence()->getId()]) . $ligne)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF6D6B8');
                        if ($iut === 'rcc') {
                            $sheet->setCellValueByColumnAndRow($tabRefCompetences[$competence->getCompetence()->getId()],
                                $ligne, $competence->getCoefficient());
                        }
                    }

                    if ($departement->getTypeStructure() === Departement::TYPE3 || $semestre->getOrdreLmd() >= 3) {
                        foreach ($sae->getApcSaeParcours() as $apcSaeParcour) {
                            if (array_key_exists($apcSaeParcour->getParcours()->getId(), $parcours)) {
                                $sheet->setCellValueByColumnAndRow($tabRefParcours[$apcSaeParcour->getParcours()->getId()],
                                    $ligne, 'X');
                            }
                        }
                    }

                    $ligne++;
                }

                $finTableau = $ligne - 1;
                // Formules coméptences
                foreach ($competences as $competence) {
                    if ($departement->getTypeStructure() === Departement::TYPE3 || $semestre->getOrdreLmd() >= 3) {
                        foreach ($parcours as $parcour) {
                            $colParc = Coordinate::stringFromColumnIndex($tabRefParcours[$parcour->getId()]);
                            $colComp = Coordinate::stringFromColumnIndex($tabRefCompetences[$competence->getCompetence()->getId()]);
                            $sheet->setCellValue($colComp . $this->tabRefTotalParcours[$parcour->getId()] + 1,
                                '=SUMIF($' . $colParc . '22:$' . $colParc . $finTableau . ',"X",' . $colComp . '22:' . $colComp . $finTableau . ')');
                            //=SOMME.SI($AP22:$AP49;"X";AT22:AT49)
                            //total des SAE
                            $sheet->setCellValue($colComp . $this->tabRefTotalParcours[$parcour->getId()] + 2,
                                '=SUMIF($' . $colParc . $debutSae . ':$' . $colParc . $finTableau . ',"X",' . $colComp . $debutSae . ':' . $colComp . $finTableau . ')');

                            $total = $colComp . $this->tabRefTotalParcours[$parcour->getId()] + 1;
                            $totalSae = $colComp . $this->tabRefTotalParcours[$parcour->getId()] + 2;
                            $cellCondition = $colComp . $this->tabRefTotalParcours[$parcour->getId()] + 3;
                            $sheet->setCellValue($cellCondition,
                                '=IFERROR(' . $totalSae . '/' . $total . ',"")');

                            $redStyle = new Style(false, true);
                            $redStyle->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getEndColor()->setARGB(Color::COLOR_RED);
                            $redStyle->getFont()->setColor(new Color(Color::COLOR_WHITE));

                            $conditionalStyles = [];
                            $wizardFactory = new Wizard('A1');
                            /** @var Wizard\Expression $expressionWizard */
                            $expressionWizard = $wizardFactory->newRule(Wizard::EXPRESSION);
                            $expressionWizard->expression('AND(ISNUMBER(' . $cellCondition . '),OR(' . $cellCondition . '<40%,' . $cellCondition . '>60%))')
                                ->setStyle($redStyle);
                            $conditionalStyles[] = $expressionWizard->getConditional();
                            $sheet
                                ->getStyle($cellCondition)
                                ->setConditionalStyles($conditionalStyles);

                        }
                    } else {
                        $finTableau = $finTableau > 49 ? $finTableau : 49;
                        $colComp = Coordinate::stringFromColumnIndex($tabRefCompetences[$competence->getCompetence()->getId()]);
                        $sheet->setCellValue($colComp . ($finTableau + 3),
                            '=SUM(' . $colComp . '22:' . $colComp . $finTableau . ')');
                        //=SOMME.SI($AP22:$AP49;"X";AT22:AT49)
                        //total des SAE
                        $sheet->setCellValue($colComp . ($finTableau + 4),
                            '=SUM(' . $colComp . $debutSae . ':' . $colComp . $finTableau . ')');

                        $total = $colComp . ($finTableau + 3);
                        $totalSae = $colComp . ($finTableau + 4);
                        $cellCondition = $colComp . ($finTableau + 5);

                        $sheet->setCellValue($cellCondition,
                            '=IFERROR(' . $totalSae . '/' . $total . ',"")');

                        $redStyle = new Style(false, true);
                        $redStyle->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getEndColor()->setARGB(Color::COLOR_RED);
                        $redStyle->getFont()->setColor(new Color(Color::COLOR_WHITE));

                        $conditionalStyles = [];
                        $wizardFactory = new Wizard('A1');
                        /** @var Wizard\Expression $expressionWizard */
                        $expressionWizard = $wizardFactory->newRule(Wizard::EXPRESSION);
                        $expressionWizard->expression('AND(ISNUMBER(' . $cellCondition . '),OR(' . $cellCondition . '<40%,' . $cellCondition . '>60%))')
                            ->setStyle($redStyle);
                        $conditionalStyles[] = $expressionWizard->getConditional();
                        $sheet
                            ->getStyle($cellCondition)
                            ->setConditionalStyles($conditionalStyles);

                        $sheet->getStyle($cellCondition)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                        $sheet->getStyle($totalSae)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                        $sheet->getStyle($total)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                    }
                }

                //vérouillage


//                for ($j = 1; $j < 55; $j++) {
//                   $sheet->getColumnDimensionByColumn($j)->setVisible(false);
////                    for ($i = 1; $i < 22; $i++) {
////                        $sheet->getRowDimension($i)->setVisible(false);
////                        $sheet->getStyle(Coordinate::stringFromColumnIndex($j) . $i)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
////                    }
//////                    $finTableau = $finTableau > 49 ? $finTableau : 49;
//////                    for ($i = $finTableau; $i < 80; $i++) {
//////                        //$sheet->getRowDimension($i)->setVisible(false);
//////                        $sheet->getStyle(Coordinate::stringFromColumnIndex($j) . $i)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
//////                    }
//                }
            }
        }

        $sheetModele = $spreadsheet->getSheetByName('modele');
        $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($sheetModele));//suppression de la page modele
        $sheetModele = $spreadsheet->getSheetByName('modele_1');
        $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($sheetModele));//suppression de la page modele
        $excelWriter->setSpreadsheet($spreadsheet, true);


        return $excelWriter->genereFichier('mcc_fi_' . $departement->getSigle());
    }

    private function borderInsideOutside($cellDebut, $cellFin, $sheet)
    {
        $sheet->getStyle($cellDebut . ':' . $cellFin)->getBorders()->getInside()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($cellDebut . ':' . $cellFin)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        return $sheet;
    }

    private function addLigneIfNececessary(mixed $sheet, int $ligne)
    {
        if ($ligne > 48) {
            $ligneBase = 48;
            $sheet->insertNewRowBefore($ligne, 1);
            for ($colCopy = 35; $colCopy < 100; $colCopy++) {
                $cellBase = Coordinate::stringFromColumnIndex($colCopy) . $ligneBase;
                $cellCopy = Coordinate::stringFromColumnIndex($colCopy) . $ligne;
                //  $sheet->setCellValue($cellCopy, $sheet->getCell($cellBase)->getValue());
                $styleArray = $sheet->getStyle($cellBase)->exportArray();
                $sheet->getStyle($cellCopy)->applyFromArray($styleArray);
                $sheet->duplicateStyle($sheet->getStyle($cellBase), $cellCopy);
            }

            foreach ($this->tabRefTotalParcours as $key => $parc) {
                $this->tabRefTotalParcours[$key] = $parc + 1;
            }
        }

        return $sheet;
    }
}
