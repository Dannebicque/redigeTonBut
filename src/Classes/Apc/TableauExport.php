<?php

namespace App\Classes\Apc;

use App\Classes\Excel\ExcelWriter;
use App\Classes\Tableau\VolumesHoraires;
use App\Entity\Annee;
use App\Entity\ApcCompetence;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\SemestreRepository;

class TableauExport
{

    protected ExcelWriter $excelWriter;
    protected SemestreRepository $semestreRepository;
    protected TableauCroise $tableauCroise;
    protected TableauPreconisation $tableauPreconisation;
    protected ApcSaeRepository $apcSaeRepository;
    protected ApcRessourceRepository $apcRessourceRepository;
    protected ApcSaeParcoursRepository $apcSaeParcoursRepository;
    protected ApcRessourceParcoursRepository $apcRessourceParcoursRepository;
    protected VolumesHoraires $volumesHoraires;

    public function __construct(
        ExcelWriter $excelWriter,
        VolumesHoraires $volumesHoraires,
        SemestreRepository $semestreRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        TableauCroise $tableauCroise,
        TableauPreconisation $tableauPreconisation
    ) {
        $this->excelWriter = $excelWriter;
        $this->volumesHoraires = $volumesHoraires;
        $this->semestreRepository = $semestreRepository;
        $this->tableauCroise = $tableauCroise;
        $this->tableauPreconisation = $tableauPreconisation;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->apcRessourceRepository = $apcRessourceRepository;
        $this->apcSaeParcoursRepository = $apcSaeParcoursRepository;
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
    }


    public function exportTableauCroise(Annee $annee, ?ApcParcours $parcours = null)
    {
        if ($parcours === null || $annee->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId()]);
        } else {
            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        }

        $this->excelWriter->nouveauFichier('');
        foreach ($semestres as $semestre) {
            $this->excelWriter->createSheet('S' . $semestre->getOrdreLmd());
            $ligne = 2;
            $this->tableauCroise->getDatas($semestre, $parcours);
            $this->excelWriter->writeCellXY(1, $ligne, 'Compétences');
            $this->excelWriter->writeCellXY(2, $ligne, 'Apprentissages critiques');
            $col = 4;
            foreach ($this->tableauCroise->getSaes() as $sae) {
                $this->excelWriter->writeCellXY($col, $ligne, $sae->getCodeMatiere() . ' - ' . $sae->getLibelle());
                $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');
                $col++;
            }
            $col++;

            foreach ($this->tableauCroise->getRessources() as $ressource) {
                $this->excelWriter->writeCellXY($col, $ligne,
                    $ressource->getCodeMatiere() . ' - ' . $ressource->getLibelle());
                $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');

                $col++;
            }
            $ligne++;
            $col = 4;

            foreach ($this->tableauCroise->getNiveaux() as $niveau) {
                $this->excelWriter->writeCellXY(1, $ligne, $niveau->getCompetence()->getNomCourt());
                $this->excelWriter->colorCellXY(1, $ligne,
                    ApcCompetence::COLOREXCEl[$niveau->getCompetence()->getCouleur()]);
                $this->excelWriter->mergeCellsCaR(1, $ligne, 1,
                    $ligne + count($niveau->getApcApprentissageCritiques()));


                foreach ($niveau->getApcApprentissageCritiques() as $ac) {
                    $this->excelWriter->writeCellXY(2, $ligne, $ac->getCode() . ' - ' . $ac->getLibelle());
                    $this->excelWriter->colorCellXY(2, $ligne,
                        ApcCompetence::COLOREXCEl[$niveau->getCompetence()->getCouleur()]);
                    foreach ($this->tableauCroise->getSaes() as $sae) {
                        if (array_key_exists($sae->getId(),
                                $this->tableauCroise->getTab()['saes']) && array_key_exists($ac->getId(),
                                $this->tableauCroise->getTab()['saes'][$sae->getId()])) {
                            $this->excelWriter->writeCellXY($col, $ligne, 'X');
                        }
                        $col++;
                    }
                    $col++;

                    foreach ($this->tableauCroise->getRessources() as $ressource) {
                        if (array_key_exists($ressource->getId(),
                                $this->tableauCroise->getTab()['ressources']) && array_key_exists($ac->getId(),
                                $this->tableauCroise->getTab()['ressources'][$ressource->getId()])) {
                            $this->excelWriter->writeCellXY($col, $ligne, 'X');
                        }
                        $col++;
                    }
                    $ligne++;
                    $col = 4;
                }

                $this->excelWriter->writeCellXY(2, $ligne, 'Coefficients');
                foreach ($this->tableauCroise->getSaes() as $sae) {
                    if (array_key_exists($niveau->getCompetence()->getId(),
                            $this->tableauCroise->getCoefficients()) && array_key_exists($sae->getId(),
                            $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['saes'])) {
                        $this->excelWriter->writeCellXY($col, $ligne,
                            $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['saes'][$sae->getId()]);
                    } else {
                        $this->excelWriter->writeCellXY($col, $ligne, 0);
                    }
                    $col++;
                }
                $col++;

                foreach ($this->tableauCroise->getRessources() as $ressource) {
                    if (array_key_exists($niveau->getCompetence()->getId(),
                            $this->tableauCroise->getCoefficients()) && array_key_exists($ressource->getId(),
                            $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['ressources'])) {
                        $this->excelWriter->writeCellXY($col, $ligne,
                            $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['ressources'][$ressource->getId()]);
                    } else {
                        $this->excelWriter->writeCellXY($col, $ligne, 0);
                    }
                    $col++;
                }
                $ligne++;
                $this->excelWriter->getColumnsAutoSizeInt(1, $col);
                $col = 4;

            }
        }


        return $this->excelWriter->genereFichier('tableau_croise_BUT' . $annee->getOrdre());
    }

    public function exportTableauHoraire(Annee $annee, ?ApcParcours $parcours)
    {

        if ($parcours === null || $annee->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId()]);
        } else {
            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        }

        $donnees = $this->volumesHoraires->setSemestres($semestres, $parcours)->getDataJson();

        $this->excelWriter->nouveauFichier('');
        foreach ($semestres as $semestre) {
            if ($parcours === null) {
                $saes = $this->apcSaeRepository->findBySemestre($semestre);
                $ressources = $this->apcRessourceRepository->findBySemestre($semestre);
            } else {
                $saes = $this->apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
                $ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
            }

            $this->excelWriter->createSheet('S' . $semestre->getOrdreLmd());
            $this->excelWriter->writeCellName('A1', 'Pôles');
            $this->excelWriter->mergeCells('A1:A2');
            $col = 2;
            $ligne = 2;
            $this->excelWriter->writeCellXY($col, 1, 'SAE', ['style' => 'HORIZONTAL_CENTER']);
            $nbSaes = count($saes);
            $nbRessources = count($ressources);

            if ($nbSaes > 0) {
                $this->excelWriter->mergeCellsCaR($col, 1, $col + $nbSaes - 1, 1);
                foreach ($saes as $sae) {
                    $this->excelWriter->writeCellXY($col, $ligne, $sae->getDisplay(), ['bgcolor' => 'ebb71a']);
                    $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');
                    $col++;
                }
                $this->excelWriter->writeCellXY(2, $ligne + 1, '', ['bgcolor' => 'ebb71a']);
                $this->excelWriter->mergeCellsCaR(2, $ligne + 1, 2 + $nbSaes - 1, $ligne + 1);
                $this->excelWriter->writeCellXY(2, $ligne + 2, '', ['bgcolor' => 'ebb71a']);
                $this->excelWriter->mergeCellsCaR(2, $ligne + 2, 2 + $nbSaes - 1, $ligne + 2);
            } else {
                $col++;
            }


            $this->excelWriter->writeCellXY($col, 1, 'Ressources', ['style' => 'HORIZONTAL_CENTER']);
            // $this->excelWriter->mergeCellsCaR(count($saes), 1, count($saes) + count($ressources)-1, 1);
            foreach ($ressources as $ressource) {
                $this->excelWriter->writeCellXY($col, $ligne, $ressource->getDisplay(), ['bgcolor' => '9ec5fe']);
                $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');
                $this->excelWriter->writeCellXY($col, $ligne + 1, $ressource->getHeuresTotales(),
                    ['style' => 'HORIZONTAL_CENTER',]);
                $this->excelWriter->writeCellXY($col, $ligne + 2, $ressource->getTpPpn(),
                    ['style' => 'HORIZONTAL_CENTER']);
                $col++;
            }
            $colonneTotal = $col;
            $this->excelWriter->writeCellXY($colonneTotal, $ligne, 'Total');
            $this->excelWriter->writeCellXY($colonneTotal, $ligne + 1,
                $donnees[$semestre->getOrdreLmd()]['totalEnseignementRessources'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal, $ligne + 2,
                $donnees[$semestre->getOrdreLmd()]['totalDontTpRessources'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne + 2,
                number_format($donnees[$semestre->getOrdreLmd()]['pourcentageTpNational'], 2),
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne + 2,
                'pourcentage de TP défini nationalement', ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);

            $ligne++;
            $col = 2;
            $this->excelWriter->writeCellXY(1, $ligne, 'Volume horaire des enseignements définis nationalement');
            $ligne++;
            $this->excelWriter->writeCellXY(1, $ligne, 'Dont TP');
            $ligne++;
            $this->excelWriter->writeCellXY(1, $ligne,
                'Volume horaire des enseignements à définir en adaptation locale');
            $this->excelWriter->mergeCellsCaR(1, $ligne, 1, $ligne + 1);
            $this->excelWriter->writeCellXY($col, $ligne,
                $donnees[$semestre->getOrdreLmd()]['vhNbHeuresEnseignementSae'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            if ($nbSaes > 0) {
                $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $nbSaes - 1, $ligne);
                $this->excelWriter->mergeCellsCaR($col + $nbSaes, $ligne, $colonneTotal - 1, $ligne);
            }
            $this->excelWriter->writeCellXY($colonneTotal, $ligne,
                $donnees[$semestre->getOrdreLmd()]['totalAdaptationLocaleEnseignement'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->mergeCellsCaR($colonneTotal, $ligne, $colonneTotal, $ligne + 1);
            $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
                number_format($donnees[$semestre->getOrdreLmd()]['pourcentageAdaptationLocaleCalcule'], 2),
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne, 'Pourcentage d\'adaptation locale (calculé)',
                ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
            $ligne++;
            $this->excelWriter->writeCellXY($col, $ligne,
                $donnees[$semestre->getOrdreLmd()]['vhNbHeureeEnseignementSaeRessource'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
            $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne, 'Rappel',
                ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
                number_format($semestre->getPourcentageAdaptationLocale(), 2) . '%',
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $ligne++;
            $this->excelWriter->writeCellXY(1, $ligne, 'Dont TP');
            $this->excelWriter->writeCellXY($col, $ligne,
                $donnees[$semestre->getOrdreLmd()]['vhNbHeuresDontTpSaeRessource'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
            $this->excelWriter->writeCellXY($colonneTotal, $ligne,
                $donnees[$semestre->getOrdreLmd()]['totalAdaptationLocaleDontTp'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
                number_format($donnees[$semestre->getOrdreLmd()]['pourcentageTpLocalement'], 2),
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne, 'pourcentage de TP défini localement',
                ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
            $ligne++;
            $this->excelWriter->writeCellXY($col, $ligne, 'Volume horaire total des enseignements (calculé)',
                ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => '75b798']);
            $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
            $this->excelWriter->writeCellXY($colonneTotal, $ligne,
                $donnees[$semestre->getOrdreLmd()]['totalEnseignements'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => '75b798']);
            $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne, $semestre->getNbHeuresRessourceSae(),
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
                'rappel volume total d\'enseignement issu du tableau global des 6 semestres',
                ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
            $ligne++;
            $this->excelWriter->writeCellXY($col, $ligne, 'Dont TP',
                ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => '75b798']);
            $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
            $this->excelWriter->writeCellXY($colonneTotal, $ligne, $donnees[$semestre->getOrdreLmd()]['totalDontTp'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => '75b798']);
            $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
                number_format($donnees[$semestre->getOrdreLmd()]['pourcentageTpLocalementNationalement'], 2),
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
                'pourcentage de tp défini localement et nationalement',
                ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
            $ligne++;
            $this->excelWriter->writeCellXY(1, $ligne, 'Volume horaire projet tuteuré');
            $this->excelWriter->writeCellXY($col, $ligne, $donnees[$semestre->getOrdreLmd()]['vhNbHeuresProjetTutores'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'CED4D7']);
            if ($nbSaes > 0) {
                $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $nbSaes - 1, $ligne);
                $this->excelWriter->writeCellXY($col + $nbSaes, $ligne, '',
                    ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'CED4D7']);
                $this->excelWriter->mergeCellsCaR($col + $nbSaes, $ligne, $colonneTotal - 1, $ligne);
            }
            $this->excelWriter->writeCellXY($colonneTotal, $ligne,
                $donnees[$semestre->getOrdreLmd()]['totalProjetTutore'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'CED4D7']);
            $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne, $semestre->getNbHeuresProjet(),
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
                'rappel volume de projet tutoré issu du tableau global des 6 semestres',
                ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
            $ligne++;
            $this->excelWriter->writeCellXY($col, $ligne, 'Volume horaire total des enseignements avec projet tuteuré',
                ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => '75b798']);
            $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
            $this->excelWriter->writeCellXY($colonneTotal, $ligne,
                $donnees[$semestre->getOrdreLmd()]['totalEnseignementProjetTutore'],
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => '75b798']);
            $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
                $semestre->getNbHeuresRessourceSae() + $semestre->getNbHeuresProjet(),
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
                'rappel volume total enseignement + projet tutoré issu du tableau global 6 semestres',
                ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
            $this->excelWriter->getColumnsAutoSizeInt(1, $colonneTotal + 2);
        }

        return $this->excelWriter->genereFichier('tableau_horaire_BUT' . $annee->getOrdre());
    }

    public function exportTableauPreconisation(Annee $annee, ?ApcParcours $parcours)
    {


        if ($parcours === null || $annee->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId()]);
        } else {
            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        }

        $this->excelWriter->nouveauFichier('');
        $preconisation = $this->tableauPreconisation->getPreconisation($semestres, $parcours);
        foreach ($semestres as $semestre) {
            $this->excelWriter->createSheet('S' . $semestre->getOrdreLmd());
            $ligne = 2;
            $this->tableauPreconisation->getDatas($semestre, $parcours);
            $this->excelWriter->writeCellXY(1, $ligne, 'Compétences');
            $this->excelWriter->writeCellXY(2, $ligne, 'UE');
            $col = 4;
            foreach ($this->tableauPreconisation->getSaes() as $sae) {
                $this->excelWriter->writeCellXY($col, $ligne, $sae->getCodeMatiere() . ' - ' . $sae->getLibelle());
                $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');
                $col++;
            }
            $col++;

            foreach ($this->tableauPreconisation->getRessources() as $ressource) {
                $this->excelWriter->writeCellXY($col, $ligne,
                    $ressource->getCodeMatiere() . ' - ' . $ressource->getLibelle());
                $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');
                $col++;
            }
            $this->excelWriter->writeCellXY($col, $ligne, 'ECTS');
            $col++;
            $this->excelWriter->writeCellXY($col, $ligne, 'Coefficients / UE');
            $col++;
            $this->excelWriter->writeCellXY($col, $ligne, 'Rapport coeffs SAÉ / coeffs UE');
            $ligne++;


            foreach ($this->tableauPreconisation->getNiveaux() as $niveau) {
                $col = 4;
                $this->excelWriter->writeCellXY(1, $ligne, $niveau->getCompetence()->getNomCourt());
                $this->excelWriter->colorCellXY(1, $ligne,
                    ApcCompetence::COLOREXCEl[$niveau->getCompetence()->getCouleur()]);

                $this->excelWriter->writeCellXY(2, $ligne,
                    'UE ' . $semestre->getOrdreLmd() . '.' . $niveau->getCompetence()->getNumero());
                $this->excelWriter->colorCellXY(2, $ligne,
                    ApcCompetence::COLOREXCEl[$niveau->getCompetence()->getCouleur()]);

                foreach ($this->tableauPreconisation->getSaes() as $sae) {
                    if (array_key_exists($semestre->getOrdreLmd(), $preconisation) &&
                        array_key_exists($sae->getId(), $preconisation[$semestre->getOrdreLmd()]['saes']) &&
                        array_key_exists($niveau->getCompetence()->getId(),
                            $preconisation[$semestre->getOrdreLmd()]['saes'][$sae->getId()])
                    ) {
                        $this->excelWriter->writeCellXY($col, $ligne,
                            $preconisation[$semestre->getOrdreLmd()]['saes'][$sae->getId()][$niveau->getCompetence()->getId()]['coefficient']);
                    } else {
                        $this->excelWriter->writeCellXY($col, $ligne, 0);
                    }
                    $col++;
                }
                $col++;

                foreach ($this->tableauPreconisation->getRessources() as $ressource) {
                    if (array_key_exists($semestre->getOrdreLmd(), $preconisation) &&
                        array_key_exists($ressource->getId(), $preconisation[$semestre->getOrdreLmd()]['ressources']) &&
                        array_key_exists($niveau->getCompetence()->getId(),
                            $preconisation[$semestre->getOrdreLmd()]['ressources'][$ressource->getId()])
                    ) {
                        $this->excelWriter->writeCellXY($col, $ligne,
                            $preconisation[$semestre->getOrdreLmd()]['ressources'][$ressource->getId()][$niveau->getCompetence()->getId()]['coefficient']);
                    } else {
                        $this->excelWriter->writeCellXY($col, $ligne, 0);
                    }
                    $col++;
                }

                if (array_key_exists($semestre->getOrdreLmd(), $preconisation) &&
                    array_key_exists($niveau->getCompetence()->getId(),
                        $preconisation[$semestre->getOrdreLmd()]['competences'])
                ) {
                    $this->excelWriter->writeCellXY($col, $ligne,
                        $preconisation[$semestre->getOrdreLmd()]['competences'][$niveau->getCompetence()->getId()]['ects']);
                } else {
                    $this->excelWriter->writeCellXY($col, $ligne, 0);
                }
                $col++;
                if (array_key_exists($semestre->getOrdreLmd(), $preconisation) &&
                    array_key_exists($niveau->getCompetence()->getId(),
                        $preconisation[$semestre->getOrdreLmd()]['competences'])
                ) {
                    $this->excelWriter->writeCellXY($col, $ligne,
                        $preconisation[$semestre->getOrdreLmd()]['competences'][$niveau->getCompetence()->getId()]['total']);
                } else {
                    $this->excelWriter->writeCellXY($col, $ligne, 0);
                }
                $col++;
                if (array_key_exists($semestre->getOrdreLmd(), $preconisation) &&
                    array_key_exists($niveau->getCompetence()->getId(),
                        $preconisation[$semestre->getOrdreLmd()]['competences'])
                ) {
                    $this->excelWriter->writeCellXY($col, $ligne,
                        $preconisation[$semestre->getOrdreLmd()]['competences'][$niveau->getCompetence()->getId()]['rapport']);
                } else {
                    $this->excelWriter->writeCellXY($col, $ligne, 0);
                }
                $ligne++;
            }
            $this->excelWriter->writeCellXY(1, $ligne, 'Poids chaque SAÉ ou ressource');
            $this->excelWriter->mergeCellsCaR(1, $ligne, 2, $ligne);
            $col = 4;
            foreach ($this->tableauPreconisation->getSaes() as $sae) {
                if (array_key_exists($semestre->getOrdreLmd(), $preconisation) &&
                    array_key_exists($sae->getId(),
                        $preconisation[$semestre->getOrdreLmd()]['saes'])
                ) {
                    $this->excelWriter->writeCellXY($col, $ligne,
                        $preconisation[$semestre->getOrdreLmd()]['saes'][$sae->getId()]['total']);
                } else {
                    $this->excelWriter->writeCellXY($col, $ligne, 0);
                }
                $col++;
            }
            $col++;

            foreach ($this->tableauPreconisation->getRessources() as $ressource) {
                if (array_key_exists($semestre->getOrdreLmd(), $preconisation) &&
                    array_key_exists($ressource->getId(),
                        $preconisation[$semestre->getOrdreLmd()]['ressources'])
                ) {
                    $this->excelWriter->writeCellXY($col, $ligne,
                        $preconisation[$semestre->getOrdreLmd()]['ressources'][$ressource->getId()]['total']);
                } else {
                    $this->excelWriter->writeCellXY($col, $ligne, 0);
                }
                $col++;
            }

            $this->excelWriter->getColumnsAutoSize('A', 'BA');
        }


        return $this->excelWriter->genereFichier('tableau_preconisation_BUT' . $annee->getOrdre());
    }

    public function exportTableauCroiseVolumeHoraire(Departement $departement)
    {
        $this->excelWriter->nouveauFichier('');
        foreach ($departement->getAnnees() as $annee) {

            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId()]);
            if ($annee->getOrdre() > 1 || $departement->getTypeStructure() === Departement::TYPE3) {
                $parcours = $departement->getApcParcours();
            }

            foreach ($semestres as $semestre) {
                //-----------------------------
                //table croisé
                //-----------------------------
                $this->excelWriter->createSheet('S' . $semestre->getOrdreLmd());
                $ligne = 2;
                if ($annee->getOrdre() > 1 || $departement->getTypeStructure() === Departement::TYPE3) {
                    foreach ($parcours as $parcour) {
                        $ligne = $this->afficheParcours($ligne, $parcour, $semestre, $semestres);
                        $ligne++;
                        $ligne++;
                    }
                } else {
                    $this->affichePasParcours($ligne, $semestre, $semestres);
                }
            }

        }

        return $this->excelWriter->genereFichier('tableau_croise__volumes_horaires_' . $departement->getSigle());
    }

    private function afficheParcours($ligne, $parcours, $semestre, $semestres)
    {
        $this->excelWriter->writeCellXY(1, $ligne, $parcours->getLibelle());
$ligne++;
        $this->tableauCroise->getDatas($semestre, $parcours);
        $this->excelWriter->writeCellXY(1, $ligne, 'Compétences');
        $this->excelWriter->writeCellXY(2, $ligne, 'Apprentissages critiques');
        $col = 3;
        foreach ($this->tableauCroise->getSaes() as $sae) {
            $this->excelWriter->writeCellXY($col, $ligne,
                $sae->getCodeMatiere() . ' - ' . $sae->getLibelle());
            $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');
            $col++;
        }


        foreach ($this->tableauCroise->getRessources() as $ressource) {
            $this->excelWriter->writeCellXY($col, $ligne,
                $ressource->getCodeMatiere() . ' - ' . $ressource->getLibelle());
            $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');

            $col++;
        }
        $ligne++;
        $col = 3;

        foreach ($this->tableauCroise->getNiveaux() as $niveau) {
            $this->excelWriter->writeCellXY(1, $ligne, $niveau->getCompetence()->getNomCourt());
            $this->excelWriter->colorCellXY(1, $ligne,
                ApcCompetence::COLOREXCEl[$niveau->getCompetence()->getCouleur()]);
            $this->excelWriter->mergeCellsCaR(1, $ligne, 1,
                $ligne + count($niveau->getApcApprentissageCritiques()));


            foreach ($niveau->getApcApprentissageCritiques() as $ac) {
                $this->excelWriter->writeCellXY(2, $ligne, $ac->getCode() . ' - ' . $ac->getLibelle());
                $this->excelWriter->colorCellXY(2, $ligne,
                    ApcCompetence::COLOREXCEl[$niveau->getCompetence()->getCouleur()]);
                foreach ($this->tableauCroise->getSaes() as $sae) {
                    if (array_key_exists($sae->getId(),
                            $this->tableauCroise->getTab()['saes']) && array_key_exists($ac->getId(),
                            $this->tableauCroise->getTab()['saes'][$sae->getId()])) {
                        $this->excelWriter->writeCellXY($col, $ligne, 'X');
                    }
                    $col++;
                }

                foreach ($this->tableauCroise->getRessources() as $ressource) {
                    if (array_key_exists($ressource->getId(),
                            $this->tableauCroise->getTab()['ressources']) && array_key_exists($ac->getId(),
                            $this->tableauCroise->getTab()['ressources'][$ressource->getId()])) {
                        $this->excelWriter->writeCellXY($col, $ligne, 'X');
                    }
                    $col++;
                }
                $ligne++;
                $col = 3;
            }

            $this->excelWriter->writeCellXY(2, $ligne, 'Coefficients');
            foreach ($this->tableauCroise->getSaes() as $sae) {
                if (array_key_exists($niveau->getCompetence()->getId(),
                        $this->tableauCroise->getCoefficients()) && array_key_exists($sae->getId(),
                        $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['saes'])) {
                    $this->excelWriter->writeCellXY($col, $ligne,
                        $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['saes'][$sae->getId()]);
                } else {
                    $this->excelWriter->writeCellXY($col, $ligne, 0);
                }
                $col++;
            }
            //  $col++;

            foreach ($this->tableauCroise->getRessources() as $ressource) {
                if (array_key_exists($niveau->getCompetence()->getId(),
                        $this->tableauCroise->getCoefficients()) && array_key_exists($ressource->getId(),
                        $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['ressources'])) {
                    $this->excelWriter->writeCellXY($col, $ligne,
                        $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['ressources'][$ressource->getId()]);
                } else {
                    $this->excelWriter->writeCellXY($col, $ligne, 0);
                }
                $col++;
            }
            $ligne++;
            $this->excelWriter->getColumnsAutoSizeInt(1, $col);
            $col = 3;
        }
        $ligne++;
        //-------------------------
        // TABLEAU VOLUMES HORAIRES
        //-------------------------
        $donnees = $this->volumesHoraires->setSemestres($semestres, $parcours)->getDataJson();
        $col = 3;
        $nbSaes = count($this->tableauCroise->getSaes());

        if ($nbSaes > 0) {
            $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $nbSaes - 1, $ligne);
            $this->excelWriter->writeCellXY(3, $ligne + 1, '', ['bgcolor' => 'ebb71a']);
            $this->excelWriter->mergeCellsCaR(3, $ligne + 1, 3 + $nbSaes - 1, $ligne + 1);
            $this->excelWriter->writeCellXY(3, $ligne + 2, '', ['bgcolor' => 'ebb71a']);
            $this->excelWriter->mergeCellsCaR(3, $ligne + 2, 3 + $nbSaes - 1, $ligne + 2);
            $col+=$nbSaes;
        } else {
            $col++;
        }

        foreach ($this->tableauCroise->getRessources() as $ressource) {

            $this->excelWriter->writeCellXY($col, $ligne + 1, $ressource->getHeuresTotales(),
                ['style' => 'HORIZONTAL_CENTER',]);
            $this->excelWriter->writeCellXY($col, $ligne + 2, $ressource->getTpPpn(),
                ['style' => 'HORIZONTAL_CENTER']);
            $col++;
        }
        $colonneTotal = $col;
        $this->excelWriter->writeCellXY($colonneTotal, $ligne + 1,
            $donnees[$semestre->getOrdreLmd()]['totalEnseignementRessources'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne + 2,
            $donnees[$semestre->getOrdreLmd()]['totalDontTpRessources'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne + 2,
            number_format($donnees[$semestre->getOrdreLmd()]['pourcentageTpNational'], 2),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne + 2,
            'pourcentage de TP défini nationalement',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);

        $ligne++;
        $col = 3;
        $this->excelWriter->writeCellXY(2, $ligne,
            'Volume horaire des enseignements définis nationalement');
        $ligne++;
        $this->excelWriter->writeCellXY(2, $ligne, 'Dont TP');
        $ligne++;
        $this->excelWriter->writeCellXY(2, $ligne,
            'Volume horaire des enseignements à définir en adaptation locale');
        $this->excelWriter->mergeCellsCaR(2, $ligne, 2, $ligne + 1);
        $this->excelWriter->writeCellXY($col, $ligne,
            $donnees[$semestre->getOrdreLmd()]['vhNbHeuresEnseignementSae'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        if ($nbSaes > 0) {
            $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $nbSaes - 1, $ligne);
            $this->excelWriter->mergeCellsCaR($col + $nbSaes, $ligne, $colonneTotal - 1, $ligne);
        }
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalAdaptationLocaleEnseignement'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->mergeCellsCaR($colonneTotal, $ligne, $colonneTotal, $ligne + 1);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
            number_format($donnees[$semestre->getOrdreLmd()]['pourcentageAdaptationLocaleCalcule'], 2),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'Pourcentage d\'adaptation locale (calculé)',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY($col, $ligne,
            $donnees[$semestre->getOrdreLmd()]['vhNbHeureeEnseignementSaeRessource'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne, 'Rappel',
            ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            number_format($semestre->getPourcentageAdaptationLocale(), 2) . '%',
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY(2, $ligne, 'Dont TP');
        $this->excelWriter->writeCellXY($col, $ligne,
            $donnees[$semestre->getOrdreLmd()]['vhNbHeuresDontTpSaeRessource'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalAdaptationLocaleDontTp'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
            number_format($donnees[$semestre->getOrdreLmd()]['pourcentageTpLocalement'], 2),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne, 'pourcentage de TP défini localement',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY($col, $ligne, 'Volume horaire total des enseignements (calculé)',
            ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => '75b798']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalEnseignements'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => '75b798']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne, $semestre->getNbHeuresRessourceSae(),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'rappel volume total d\'enseignement issu du tableau global des 6 semestres',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY($col, $ligne, 'Dont TP',
            ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => '75b798']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalDontTp'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => '75b798']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
            number_format($donnees[$semestre->getOrdreLmd()]['pourcentageTpLocalementNationalement'], 2),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'pourcentage de tp défini localement et nationalement',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY(2, $ligne, 'Volume horaire projet tuteuré');
        $this->excelWriter->writeCellXY($col, $ligne,
            $donnees[$semestre->getOrdreLmd()]['vhNbHeuresProjetTutores'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'CED4D7']);
        if ($nbSaes > 0) {
            $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $nbSaes - 1, $ligne);
            $this->excelWriter->writeCellXY($col + $nbSaes, $ligne, '',
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'CED4D7']);
            $this->excelWriter->mergeCellsCaR($col + $nbSaes, $ligne, $colonneTotal - 1, $ligne);
        }
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalProjetTutore'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'CED4D7']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne, $semestre->getNbHeuresProjet(),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'rappel volume de projet tutoré issu du tableau global des 6 semestres',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY($col, $ligne,
            'Volume horaire total des enseignements avec projet tuteuré',
            ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => '75b798']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalEnseignementProjetTutore'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => '75b798']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
            $semestre->getNbHeuresRessourceSae() + $semestre->getNbHeuresProjet(),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'rappel volume total enseignement + projet tutoré issu du tableau global 6 semestres',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->getColumnsAutoSizeInt(1, $colonneTotal + 2);

        return $ligne;
    }

    private function affichePasParcours($ligne, $semestre, $semestres)
    {
        $this->tableauCroise->getDatas($semestre);
        $this->excelWriter->writeCellXY(1, $ligne, 'Compétences');
        $this->excelWriter->writeCellXY(2, $ligne, 'Apprentissages critiques');
        $col = 3;
        foreach ($this->tableauCroise->getSaes() as $sae) {
            $this->excelWriter->writeCellXY($col, $ligne,
                $sae->getCodeMatiere() . ' - ' . $sae->getLibelle());
            $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');
            $col++;
        }


        foreach ($this->tableauCroise->getRessources() as $ressource) {
            $this->excelWriter->writeCellXY($col, $ligne,
                $ressource->getCodeMatiere() . ' - ' . $ressource->getLibelle());
            $this->excelWriter->orientationCellXY($col, $ligne, 'vertical');

            $col++;
        }
        $ligne++;
        $col = 3;

        foreach ($this->tableauCroise->getNiveaux() as $niveau) {
            $this->excelWriter->writeCellXY(1, $ligne, $niveau->getCompetence()->getNomCourt());
            $this->excelWriter->colorCellXY(1, $ligne,
                ApcCompetence::COLOREXCEl[$niveau->getCompetence()->getCouleur()]);
            $this->excelWriter->mergeCellsCaR(1, $ligne, 1,
                $ligne + count($niveau->getApcApprentissageCritiques()));


            foreach ($niveau->getApcApprentissageCritiques() as $ac) {
                $this->excelWriter->writeCellXY(2, $ligne, $ac->getCode() . ' - ' . $ac->getLibelle());
                $this->excelWriter->colorCellXY(2, $ligne,
                    ApcCompetence::COLOREXCEl[$niveau->getCompetence()->getCouleur()]);
                foreach ($this->tableauCroise->getSaes() as $sae) {
                    if (array_key_exists($sae->getId(),
                            $this->tableauCroise->getTab()['saes']) && array_key_exists($ac->getId(),
                            $this->tableauCroise->getTab()['saes'][$sae->getId()])) {
                        $this->excelWriter->writeCellXY($col, $ligne, 'X');
                    }
                    $col++;
                }

                foreach ($this->tableauCroise->getRessources() as $ressource) {
                    if (array_key_exists($ressource->getId(),
                            $this->tableauCroise->getTab()['ressources']) && array_key_exists($ac->getId(),
                            $this->tableauCroise->getTab()['ressources'][$ressource->getId()])) {
                        $this->excelWriter->writeCellXY($col, $ligne, 'X');
                    }
                    $col++;
                }
                $ligne++;
                $col = 3;
            }

            $this->excelWriter->writeCellXY(2, $ligne, 'Coefficients');
            foreach ($this->tableauCroise->getSaes() as $sae) {
                if (array_key_exists($niveau->getCompetence()->getId(),
                        $this->tableauCroise->getCoefficients()) && array_key_exists($sae->getId(),
                        $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['saes'])) {
                    $this->excelWriter->writeCellXY($col, $ligne,
                        $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['saes'][$sae->getId()]);
                } else {
                    $this->excelWriter->writeCellXY($col, $ligne, 0);
                }
                $col++;
            }

            foreach ($this->tableauCroise->getRessources() as $ressource) {
                if (array_key_exists($niveau->getCompetence()->getId(),
                        $this->tableauCroise->getCoefficients()) && array_key_exists($ressource->getId(),
                        $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['ressources'])) {
                    $this->excelWriter->writeCellXY($col, $ligne,
                        $this->tableauCroise->getCoefficients()[$niveau->getCompetence()->getId()]['ressources'][$ressource->getId()]);
                } else {
                    $this->excelWriter->writeCellXY($col, $ligne, 0);
                }
                $col++;
            }
            $ligne++;
            $this->excelWriter->getColumnsAutoSizeInt(1, $col);
            $col = 3;
        }
        $ligne++;
        //-------------------------
        // TABLEAU VOLUMES HORAIRES
        //-------------------------
        $donnees = $this->volumesHoraires->setSemestres($semestres)->getDataJson();
        $col = 3;
        $nbSaes = count($this->tableauCroise->getSaes());

        if ($nbSaes > 0) {
            $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $nbSaes - 1, $ligne);
            $this->excelWriter->writeCellXY(3, $ligne + 1, '', ['bgcolor' => 'ebb71a']);
            $this->excelWriter->mergeCellsCaR(3, $ligne + 1, 3 + $nbSaes - 1, $ligne + 1);
            $this->excelWriter->writeCellXY(3, $ligne + 2, '', ['bgcolor' => 'ebb71a']);
            $this->excelWriter->mergeCellsCaR(3, $ligne + 2, 3 + $nbSaes - 1, $ligne + 2);
            $col+=$nbSaes;
        } else {
            $col++;
        }

        foreach ($this->tableauCroise->getRessources() as $ressource) {

            $this->excelWriter->writeCellXY($col, $ligne + 1, $ressource->getHeuresTotales(),
                ['style' => 'HORIZONTAL_CENTER',]);
            $this->excelWriter->writeCellXY($col, $ligne + 2, $ressource->getTpPpn(),
                ['style' => 'HORIZONTAL_CENTER']);
            $col++;
        }
        $colonneTotal = $col;
        $this->excelWriter->writeCellXY($colonneTotal, $ligne + 1,
            $donnees[$semestre->getOrdreLmd()]['totalEnseignementRessources'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne + 2,
            $donnees[$semestre->getOrdreLmd()]['totalDontTpRessources'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne + 2,
            number_format($donnees[$semestre->getOrdreLmd()]['pourcentageTpNational'], 2),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne + 2,
            'pourcentage de TP défini nationalement',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);

        $ligne++;
        $col = 3;
        $this->excelWriter->writeCellXY(2, $ligne,
            'Volume horaire des enseignements définis nationalement');
        $ligne++;
        $this->excelWriter->writeCellXY(2, $ligne, 'Dont TP');
        $ligne++;
        $this->excelWriter->writeCellXY(2, $ligne,
            'Volume horaire des enseignements à définir en adaptation locale');
        $this->excelWriter->mergeCellsCaR(2, $ligne, 2, $ligne + 1);
        $this->excelWriter->writeCellXY($col, $ligne,
            $donnees[$semestre->getOrdreLmd()]['vhNbHeuresEnseignementSae'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        if ($nbSaes > 0) {
            $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $nbSaes - 1, $ligne);
            $this->excelWriter->mergeCellsCaR($col + $nbSaes, $ligne, $colonneTotal - 1, $ligne);
        }
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalAdaptationLocaleEnseignement'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->mergeCellsCaR($colonneTotal, $ligne, $colonneTotal, $ligne + 1);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
            number_format($donnees[$semestre->getOrdreLmd()]['pourcentageAdaptationLocaleCalcule'], 2),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'Pourcentage d\'adaptation locale (calculé)',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY($col, $ligne,
            $donnees[$semestre->getOrdreLmd()]['vhNbHeureeEnseignementSaeRessource'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne, 'Rappel',
            ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            number_format($semestre->getPourcentageAdaptationLocale(), 2) . '%',
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY(2, $ligne, 'Dont TP');
        $this->excelWriter->writeCellXY($col, $ligne,
            $donnees[$semestre->getOrdreLmd()]['vhNbHeuresDontTpSaeRessource'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalAdaptationLocaleDontTp'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
            number_format($donnees[$semestre->getOrdreLmd()]['pourcentageTpLocalement'], 2),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne, 'pourcentage de TP défini localement',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY($col, $ligne, 'Volume horaire total des enseignements (calculé)',
            ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => '75b798']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalEnseignements'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => '75b798']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne, $semestre->getNbHeuresRessourceSae(),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'rappel volume total d\'enseignement issu du tableau global des 6 semestres',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY($col, $ligne, 'Dont TP',
            ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => '75b798']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalDontTp'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => '75b798']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
            number_format($donnees[$semestre->getOrdreLmd()]['pourcentageTpLocalementNationalement'], 2),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'pourcentage de tp défini localement et nationalement',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY(2, $ligne, 'Volume horaire projet tuteuré');
        $this->excelWriter->writeCellXY($col, $ligne,
            $donnees[$semestre->getOrdreLmd()]['vhNbHeuresProjetTutores'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'CED4D7']);
        if ($nbSaes > 0) {
            $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $nbSaes - 1, $ligne);
            $this->excelWriter->writeCellXY($col + $nbSaes, $ligne, '',
                ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'CED4D7']);
            $this->excelWriter->mergeCellsCaR($col + $nbSaes, $ligne, $colonneTotal - 1, $ligne);
        }
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalProjetTutore'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'CED4D7']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne, $semestre->getNbHeuresProjet(),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'rappel volume de projet tutoré issu du tableau global des 6 semestres',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $ligne++;
        $this->excelWriter->writeCellXY($col, $ligne,
            'Volume horaire total des enseignements avec projet tuteuré',
            ['style' => 'HORIZONTAL_RIGHT', 'bgcolor' => '75b798']);
        $this->excelWriter->mergeCellsCaR($col, $ligne, $colonneTotal - 1, $ligne);
        $this->excelWriter->writeCellXY($colonneTotal, $ligne,
            $donnees[$semestre->getOrdreLmd()]['totalEnseignementProjetTutore'],
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => '75b798']);
        $this->excelWriter->writeCellXY($colonneTotal + 1, $ligne,
            $semestre->getNbHeuresRessourceSae() + $semestre->getNbHeuresProjet(),
            ['style' => 'HORIZONTAL_CENTER', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->writeCellXY($colonneTotal + 2, $ligne,
            'rappel volume total enseignement + projet tutoré issu du tableau global 6 semestres',
            ['style' => 'HORIZONTAL_LEFT', 'bgcolor' => 'ebb71a']);
        $this->excelWriter->getColumnsAutoSizeInt(1, $colonneTotal + 2);

        return $ligne;
    }
}
