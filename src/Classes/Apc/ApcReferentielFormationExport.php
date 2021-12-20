<?php

namespace App\Classes\Apc;

use App\Classes\Excel\ExcelWriter;
use App\Classes\Word\MyWord;
use App\Entity\Departement;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use ZipArchive;

class ApcReferentielFormationExport
{
    protected ApcRessourceRepository $apcRessourceRepository;
    protected ApcRessourceParcoursRepository $apcRessourceParcoursRepository;
    protected ApcSaeParcoursRepository $apcSaeParcoursRepository;
    protected ApcSaeRepository $apcSaeRepository;
    protected MyWord $myWord;
    protected ExcelWriter $excelWriter;
    protected mixed $ressources;
    protected mixed $saes;
    private string $dir;

    public function __construct(
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository,
        KernelInterface $kernel,
        MyWord $myWord,
        ExcelWriter $excelWriter,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository
    ) {
        $this->apcRessourceRepository = $apcRessourceRepository;
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
        $this->apcSaeParcoursRepository = $apcSaeParcoursRepository;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->myWord = $myWord;
        $this->excelWriter = $excelWriter;
        $this->dir = $kernel->getProjectDir() . '/public/upload/zip/';
    }


    public function export(Departement $departement, string $_format)
    {

        $this->ressources = $this->apcRessourceRepository->findByDepartement($departement);
        $this->saes = $this->apcSaeRepository->findByDepartement($departement);


        switch ($_format) {
            case 'docx':
                return $this->exportZipWord();
                break;
            case 'xlsx':
                return $this->exportExcel();
                break;

        }


    }

    private function exportZipWord()
    {
        $zip = new ZipArchive();
        $fileName = 'formation-' . date('YmdHis') . '.zip';
        // The name of the Zip documents.
        $zipName = $this->dir . $fileName;

        $zip->open($zipName, ZipArchive::CREATE);
        $tabFiles = [];

        foreach ($this->ressources as $ressource) {
            $fichier = $this->myWord->exportAndSaveressource($ressource, $this->dir);
            $nomfichier = 'ressource_' . $ressource->getCodeMatiere() . '.docx';
            $tabFiles[] = $fichier;
            $zip->addFile($fichier, $nomfichier);
        }

        foreach ($this->saes as $sae) {
            $fichier = $this->myWord->exportAndSaveSae($sae, $this->dir);
            $nomfichier = 'sae_' . $sae->getCodeMatiere() . '.docx';
            $tabFiles[] = $fichier;
            $zip->addFile($fichier, $nomfichier);
        }

        $zip->close();

        foreach ($tabFiles as $file) {
            unlink($file);
        }


        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Content-length', filesize($zipName));

        return $response;
    }

    private function exportExcel()
    {
        $this->excelWriter->nouveauFichier('');
        $this->excelWriter->createSheet('Ressources');

        $this->excelWriter->writeCellName('A1', 'Semestre');
        $this->excelWriter->writeCellName('B1', 'Code');
        $this->excelWriter->writeCellName('C1', 'Libellé');
        $this->excelWriter->writeCellName('D1', 'Libellé court');
        $this->excelWriter->writeCellName('E1', 'Ordre');
        $this->excelWriter->writeCellName('F1', 'Compétence 1');
        $this->excelWriter->writeCellName('G1', 'Compétence 2');
        $this->excelWriter->writeCellName('H1', 'Compétence 3');
        $this->excelWriter->writeCellName('I1', 'Compétence 4');
        $this->excelWriter->writeCellName('J1', 'Compétence 5');
        $this->excelWriter->writeCellName('K1', 'Compétence 6');
        $this->excelWriter->writeCellName('L1', 'SAE concerncées');
        $this->excelWriter->writeCellName('M1', 'Ressources prérequises');
        $this->excelWriter->writeCellName('N1', 'heures totales');
        $this->excelWriter->writeCellName('O1', 'dont heures TP');
        $this->excelWriter->writeCellName('P1', 'Description');
        $this->excelWriter->writeCellName('Q1', 'mots clés');
        $this->excelWriter->writeCellName('R1', 'parcours concernés');
        $this->excelWriter->writeCellName('S1', 'H CM Préco.');
        $this->excelWriter->writeCellName('T1', 'H TD Préco.');
        $this->excelWriter->writeCellName('U1', 'H TP Préco.');
        $ligne = 2;
        /** @var \App\Entity\ApcRessource $ressource */
        foreach ($this->ressources as $ressource) {

            $this->excelWriter->writeCellName('A' . $ligne, $ressource->getSemestre()?->getLibelle());
            $this->excelWriter->writeCellName('B' . $ligne, $ressource->getCodeMatiere());
            $this->excelWriter->writeCellName('C' . $ligne, $ressource->getLibelle());
            $this->excelWriter->writeCellName('D' . $ligne, $ressource->getLibelleCourt());
            $this->excelWriter->writeCellName('E' . $ligne, $ressource->getOrdre());

            $tComp = [];
            foreach ($ressource->getApcRessourceApprentissageCritiques() as $ac) {
                if (!array_key_exists($ac->getApprentissageCritique()->getCompetence()->getCouleur(), $tComp)) {
                    $tComp[$ac->getApprentissageCritique()->getCompetence()->getCouleur()] = [];
                }
                $tComp[$ac->getApprentissageCritique()->getCompetence()->getCouleur()][] = $ac->getApprentissageCritique()->getCode();
            }

            for ($i = 1; $i <= 6; $i++) {
                $comp = '';
                if (array_key_exists('c' . $i, $tComp)) {
                    $comp = implode(';', $tComp['c' . $i]);
                }
                $this->excelWriter->writeCellXY(5 + $i, $ligne, $comp);
            }

            $saes = '';
            foreach ($ressource->getApcSaeRessources() as $apcSaeRessource) {
                $saes .= $apcSaeRessource->getSae()->getCodeMatiere() . ';';
            }
            $this->excelWriter->writeCellName('L' . $ligne, $saes);

            $prerequis = '';
            foreach ($ressource->getRessourcesPreRequises() as $apcRessources) {
                $prerequis .= $apcRessources->getCodeMatiere() . ';';
            }
            $this->excelWriter->writeCellName('M' . $ligne, $prerequis);
            $this->excelWriter->writeCellName('N' . $ligne, $ressource->getHeuresTotales());
            $this->excelWriter->writeCellName('O' . $ligne, $ressource->getTpPpn());
            $this->excelWriter->writeCellName('P' . $ligne, $ressource->getDescription());
            $this->excelWriter->writeCellName('Q' . $ligne, $ressource->getMotsCles());

            $parcours = '';
            foreach ($ressource->getApcRessourceParcours() as $apcRessourceParcour) {
                $parcours .= $apcRessourceParcour->getParcours()?->getLibelle() . ';';
            }

            $this->excelWriter->writeCellName('R' . $ligne, $parcours);
            $this->excelWriter->writeCellName('S' . $ligne, $ressource->getCmPreco());
            $this->excelWriter->writeCellName('T' . $ligne, $ressource->getTdPreco());
            $this->excelWriter->writeCellName('U' . $ligne, $ressource->getTpPreco());
            $ligne++;
        }
        $this->excelWriter->getColumnsAutoSize('A', 'U');

        $this->excelWriter->createSheet('Saes');
//Semestre	Code	Libellé	Libellé court	Ordre	Compétence 1	Compétence 2	Compétence 3	Compétence 4	Compétence 5	Compétence 6	Ressources	parcours concernés	Objectifs	Descriptif	HeuresTotales	DontTp	PrécoProjet	Préco Exemples
        $this->excelWriter->writeCellName('A1', 'Semestre');
        $this->excelWriter->writeCellName('B1', 'Code');
        $this->excelWriter->writeCellName('C1', 'Libellé');
        $this->excelWriter->writeCellName('D1', 'Libellé court');
        $this->excelWriter->writeCellName('E1', 'Ordre');
        $this->excelWriter->writeCellName('F1', 'Compétence 1');
        $this->excelWriter->writeCellName('G1', 'Compétence 2');
        $this->excelWriter->writeCellName('H1', 'Compétence 3');
        $this->excelWriter->writeCellName('I1', 'Compétence 4');
        $this->excelWriter->writeCellName('J1', 'Compétence 5');
        $this->excelWriter->writeCellName('K1', 'Compétence 6');
        $this->excelWriter->writeCellName('L1', 'Ressources');
        $this->excelWriter->writeCellName('M1', 'parcours concernés');
        $this->excelWriter->writeCellName('N1', 'Objectifs');
        $this->excelWriter->writeCellName('O1', 'Descriptif');
        $this->excelWriter->writeCellName('P1', 'Préco. Heures Totales');
        $this->excelWriter->writeCellName('Q1', 'Préco. Dont Tp');
        $this->excelWriter->writeCellName('R1', 'Préco. Heures Projet.');
        $this->excelWriter->writeCellName('S1', 'Préco. Exemple');
        $ligne = 2;
        /** @var \App\Entity\ApcSae $sae */
        foreach ($this->saes as $sae) {

            $this->excelWriter->writeCellName('A' . $ligne, $sae->getSemestre()?->getLibelle());
            $this->excelWriter->writeCellName('B' . $ligne, $sae->getCodeMatiere());
            $this->excelWriter->writeCellName('C' . $ligne, $sae->getLibelle());
            $this->excelWriter->writeCellName('D' . $ligne, $sae->getLibelleCourt());
            $this->excelWriter->writeCellName('E' . $ligne, $sae->getOrdre());

            $tComp = [];
            foreach ($sae->getApcSaeApprentissageCritiques() as $ac) {
                if (!array_key_exists($ac->getApprentissageCritique()->getCompetence()->getCouleur(), $tComp)) {
                    $tComp[$ac->getApprentissageCritique()->getCompetence()->getCouleur()] = [];
                }
                $tComp[$ac->getApprentissageCritique()->getCompetence()->getCouleur()][] = $ac->getApprentissageCritique()->getCode();
            }

            for ($i = 1; $i <= 6; $i++) {
                $comp = '';
                if (array_key_exists('c' . $i, $tComp)) {
                    $comp = implode(';', $tComp['c' . $i]);
                }
                $this->excelWriter->writeCellXY(5 + $i, $ligne, $comp);
            }

            $ressources = '';
            foreach ($sae->getApcSaeRessources() as $apcRessources) {
                $ressources .= $apcRessources->getRessource()->getCodeMatiere() . ';';
            }
            $this->excelWriter->writeCellName('L' . $ligne, $ressources);

            $parcours = '';
            foreach ($sae->getApcSaeParcours() as $apcSaeParcour) {
                $parcours .= $apcSaeParcour->getParcours()->getLibelle() . ';';
            }
            $this->excelWriter->writeCellName('M' . $ligne, $parcours);

            $this->excelWriter->writeCellName('N' . $ligne, $sae->getObjectifs());
            $this->excelWriter->writeCellName('O' . $ligne, $sae->getDescription());
            $this->excelWriter->writeCellName('P' . $ligne, $sae->getHeuresTotales());
            $this->excelWriter->writeCellName('Q' . $ligne, $sae->getTpPpn());
            $this->excelWriter->writeCellName('R' . $ligne, $sae->getProjetPpn());
            $this->excelWriter->writeCellName('S' . $ligne, $sae->getExemples());
            $ligne++;
        }
        $this->excelWriter->getColumnsAutoSize('A', 'S');


        return $this->excelWriter->genereFichier('tableau_referentiel_formation' . date('YmdHis'));
    }

    public function exportSynthese(Departement $departement)
    {
        $this->excelWriter->nouveauFichier('');
        $this->excelWriter->createSheet('Synthèse');

        $ligne = 1;
        foreach ($departement->getApcParcours() as $parcours) {
            $this->excelWriter->writeCellXY(1, $ligne, $parcours->getLibelle());
            $ligne++;
            if ($departement->getTypeStructure() === Departement::TYPE3) {
                /** @var \App\Entity\Semestre $semestre */
                foreach ($parcours->getSemestres() as $semestre) {
                    $this->excelWriter->writeCellXY(1, $ligne, $semestre->getLibelle());
                    $ligne++;
                    $ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
                    $this->excelWriter->writeCellXY(1, $ligne, 'Code');
                    $this->excelWriter->writeCellXY(2, $ligne, 'Libellé');
                    $this->excelWriter->writeCellXY(3, $ligne, 'Volume total');
                    $this->excelWriter->writeCellXY(4, $ligne, 'Dont TP');
                    $ligne++;
                    foreach ($ressources as $ressource) {
                        $this->excelWriter->writeCellXY(1, $ligne, $ressource->getCodeMatiere());
                        $this->excelWriter->writeCellXY(2, $ligne, $ressource->getLibelle());
                        $this->excelWriter->writeCellXY(3, $ligne, $ressource->getHeuresTotales());
                        $this->excelWriter->writeCellXY(4, $ligne, $ressource->getTpPpn());
                        $ligne++;
                    }
                    $saes = $this->apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
                    $this->excelWriter->writeCellXY(1, $ligne, 'Code');
                    $this->excelWriter->writeCellXY(2, $ligne, 'Libellé');
                    $ligne++;
                    foreach ($saes as $sae) {
                        $this->excelWriter->writeCellXY(1, $ligne, $sae->getCodeMatiere());
                        $this->excelWriter->writeCellXY(2, $ligne, $sae->getLibelle());
                        $ligne++;
                    }
                }
            } else {
                /** @var \App\Entity\Semestre $semestre */
                foreach ($departement->getSemestres() as $semestre) {
                    $this->excelWriter->writeCellXY(1, $ligne, $semestre->getLibelle());
                    $ligne++;
                    if ($semestre->getAnnee()->getOrdre() > 1) {
                        $ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
                        $saes = $this->apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
                    } else {
                        $ressources = $this->apcRessourceRepository->findBySemestre($semestre);
                        $saes = $this->apcSaeRepository->findBySemestre($semestre);
                    }
                    $this->excelWriter->writeCellXY(1, $ligne, 'Code');
                    $this->excelWriter->writeCellXY(2, $ligne, 'Libellé');
                    $this->excelWriter->writeCellXY(3, $ligne, 'Volume total');
                    $this->excelWriter->writeCellXY(4, $ligne, 'Dont TP');
                    $ligne++;
                    foreach ($ressources as $ressource) {
                        $this->excelWriter->writeCellXY(1, $ligne, $ressource->getCodeMatiere());
                        $this->excelWriter->writeCellXY(2, $ligne, $ressource->getLibelle());
                        $this->excelWriter->writeCellXY(3, $ligne, $ressource->getHeuresTotales());
                        $this->excelWriter->writeCellXY(4, $ligne, $ressource->getTpPpn());
                        $ligne++;
                    }

                    $this->excelWriter->writeCellXY(1, $ligne, 'Code');
                    $this->excelWriter->writeCellXY(2, $ligne, 'Libellé');
                    $ligne++;
                    foreach ($saes as $sae) {
                        $this->excelWriter->writeCellXY(1, $ligne, $sae->getCodeMatiere());
                        $this->excelWriter->writeCellXY(2, $ligne, $sae->getLibelle());
                        $ligne++;
                    }
                }
            }
        }

        $this->excelWriter->getColumnsAutoSize('A', 'S');

        return $this->excelWriter->genereFichier('tableau_referentiel_synthese_formation' . date('YmdHis'));
    }

    public function exportSyntheseAcd(Departement $departement)
    {
        $this->excelWriter->nouveauFichier('');
        $this->excelWriter->createSheet('Synthèse');

        $ligne = 1;
        foreach ($departement->getApcParcours() as $parcours) {
            $this->excelWriter->writeCellXY(1, $ligne, $parcours->getLibelle());
            $ligne++;
            if ($departement->getTypeStructure() === Departement::TYPE3) {
                /** @var \App\Entity\Semestre $semestre */
                foreach ($parcours->getSemestres() as $semestre) {
                    $this->excelWriter->writeCellXY(1, $ligne, $semestre->getLibelle());
                    $ligne++;
                    $ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
                    $this->excelWriter->writeCellXY(1, $ligne, 'Code');
                    $this->excelWriter->writeCellXY(2, $ligne, 'Libellé');
                    $this->excelWriter->writeCellXY(3, $ligne, 'Volume total');
                    $this->excelWriter->writeCellXY(4, $ligne, 'Dont TP');
                    $ligne++;
                    foreach ($ressources as $ressource) {
                        $this->excelWriter->writeCellXY(1, $ligne, $ressource->getCodeMatiere());
                        $this->excelWriter->writeCellXY(2, $ligne, $ressource->getLibelle());
                        $this->excelWriter->writeCellXY(3, $ligne, $ressource->getHeuresTotales());
                        $this->excelWriter->writeCellXY(4, $ligne, $ressource->getTpPpn());
                        $ligne++;
                    }
                    $saes = $this->apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
                    $this->excelWriter->writeCellXY(1, $ligne, 'Code');
                    $this->excelWriter->writeCellXY(2, $ligne, 'Libellé');
                    $ligne++;
                    foreach ($saes as $sae) {
                        $this->excelWriter->writeCellXY(1, $ligne, $sae->getCodeMatiere());
                        $this->excelWriter->writeCellXY(2, $ligne, $sae->getLibelle());
                        $ligne++;
                    }
                }
            } else {
                /** @var \App\Entity\Semestre $semestre */
                foreach ($departement->getSemestres() as $semestre) {
                    $this->excelWriter->writeCellXY(1, $ligne, $semestre->getLibelle());
                    $ligne++;
                    if ($semestre->getAnnee()->getOrdre() > 1) {
                        $ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
                        $saes = $this->apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
                    } else {
                        $ressources = $this->apcRessourceRepository->findBySemestre($semestre);
                        $saes = $this->apcSaeRepository->findBySemestre($semestre);
                    }
                    $this->excelWriter->writeCellXY(1, $ligne, 'Code');
                    $this->excelWriter->writeCellXY(2, $ligne, 'Libellé');
                    $this->excelWriter->writeCellXY(3, $ligne, 'Libellé Court');
                    $this->excelWriter->writeCellXY(4, $ligne, 'Volume total');
                    $this->excelWriter->writeCellXY(5, $ligne, 'Dont TP');
                    $this->excelWriter->writeCellXY(7, $ligne, 'Préco CM');
                    $this->excelWriter->writeCellXY(8, $ligne, 'Préco TD');
                    $this->excelWriter->writeCellXY(9, $ligne, 'Préco TP');
                    $ligne++;
                    foreach ($ressources as $ressource) {
                        $this->excelWriter->writeCellXY(1, $ligne, $ressource->getCodeMatiere());
                        $this->excelWriter->writeCellXY(2, $ligne, $ressource->getLibelle());
                        $this->excelWriter->writeCellXY(3, $ligne, $ressource->getLibelleCourt());
                        $this->excelWriter->writeCellXY(4, $ligne, $ressource->getHeuresTotales());
                        $this->excelWriter->writeCellXY(5, $ligne, $ressource->getTpPpn());
                        $this->excelWriter->writeCellXY(7, $ligne, $ressource->getCmPreco());
                        $this->excelWriter->writeCellXY(8, $ligne, $ressource->getTdPreco());
                        $this->excelWriter->writeCellXY(9, $ligne, $ressource->getTpPreco());
                        $ligne++;
                    }

                    $this->excelWriter->writeCellXY(1, $ligne, 'Code');
                    $this->excelWriter->writeCellXY(2, $ligne, 'Libellé');
                    $this->excelWriter->writeCellXY(3, $ligne, 'Libellé Court');
                    $this->excelWriter->writeCellXY(7, $ligne, 'Préco Volume total');
                    $this->excelWriter->writeCellXY(9, $ligne, 'Préco TP');
                    $this->excelWriter->writeCellXY(10, $ligne, 'Préco Ptut');
                    $ligne++;
                    foreach ($saes as $sae) {
                        $this->excelWriter->writeCellXY(1, $ligne, $sae->getCodeMatiere());
                        $this->excelWriter->writeCellXY(2, $ligne, $sae->getLibelle());
                        $this->excelWriter->writeCellXY(3, $ligne, $sae->getLibelleCourt());
                        $this->excelWriter->writeCellXY(7, $ligne, $sae->getHeuresTotales());
                        $this->excelWriter->writeCellXY(9, $ligne, $sae->getTpPpn());
                        $this->excelWriter->writeCellXY(10, $ligne, $sae->getProjetPpn());
                        $ligne++;
                    }
                }
            }
        }

        $this->excelWriter->getColumnsAutoSize('A', 'S');

        return $this->excelWriter->genereFichier('tableau_referentiel_synthese_formation' . date('YmdHis'));
    }
}
