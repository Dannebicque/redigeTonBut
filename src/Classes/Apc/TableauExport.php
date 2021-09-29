<?php

namespace App\Classes\Apc;

use App\Classes\Excel\ExcelWriter;
use App\Entity\Annee;
use App\Entity\ApcCompetence;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Repository\SemestreRepository;

class TableauExport
{

    protected ExcelWriter $excelWriter;
    protected SemestreRepository $semestreRepository;
    protected TableauCroise $tableauCroise;
    protected TableauPreconisation $tableauPreconisation;

    public function __construct(
        ExcelWriter $excelWriter,
        SemestreRepository $semestreRepository,
        TableauCroise $tableauCroise,
        TableauPreconisation $tableauPreconisation
    ) {
        $this->excelWriter = $excelWriter;
        $this->semestreRepository = $semestreRepository;
        $this->tableauCroise = $tableauCroise;
        $this->tableauPreconisation = $tableauPreconisation;
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
                $this->excelWriter->writeCellXY($col, $ligne, $sae->getCodeMatiere());
                $col++;
            }
            $col++;

            foreach ($this->tableauCroise->getRessources() as $ressource) {
                $this->excelWriter->writeCellXY($col, $ligne, $ressource->getCodeMatiere());
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
                $col = 4;
                $this->excelWriter->getColumnsAutoSize('A', 'BA');
            }
        }


        return $this->excelWriter->genereFichier('tableau_croise_BUT' . $annee->getOrdre());
    }

    public function exportTableauHoraire(Annee $annee, ?ApcParcours $parcours)
    {
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
                $this->excelWriter->writeCellXY($col, $ligne, $sae->getCodeMatiere());
                $col++;
            }
            $col++;

            foreach ($this->tableauPreconisation->getRessources() as $ressource) {
                $this->excelWriter->writeCellXY($col, $ligne, $ressource->getCodeMatiere());
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
}
