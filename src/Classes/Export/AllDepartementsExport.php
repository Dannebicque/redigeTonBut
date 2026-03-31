<?php

namespace App\Classes\Export;

use App\Classes\Apc\ApcStructure;
use App\Classes\Excel\ExcelWriter;
use App\Entity\Version;
use App\Repository\VersionRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class AllDepartementsExport
{
    public function __construct(
        private ExcelWriter       $excelWriter,
        private ApcStructure      $apcStructure,
        private VersionRepository $versionRepository,

    )
    {
    }


    public function exportCompetences(Version $version): StreamedResponse
    {
        $versions = $this->versionRepository->findByVersion($version->getAnnee());
        $this->excelWriter->nouveauFichier();

        foreach ($versions as $version) {
            $tParcours = $this->apcStructure->parcoursNiveaux($version);
            $departement = $version->getDepartement();
            $this->excelWriter->createSheet($departement->getSigle());

            $ligne = 1;

            //Parcours	Compétence	définition	Composantes Essentielles	Situations Professionnelles	Niveaux	Apprentissages critiques niveau 1
            $this->excelWriter->writeCellXY('A', $ligne, 'Parcours');
            $this->excelWriter->writeCellXY('B', $ligne, 'Compétence');
            $this->excelWriter->writeCellXY('C', $ligne, 'Définition');
            $this->excelWriter->writeCellXY('D', $ligne, 'Composantes Essentielles');
            $this->excelWriter->writeCellXY('E', $ligne, 'Situations Professionnelles');
            $this->excelWriter->writeCellXY('F', $ligne, 'Niveaux');
            $this->excelWriter->writeCellXY('G', $ligne, 'Apprentissages critiques');

            $ligne++;

            foreach ($version->getApcParcours() as $apcParcour) {
                foreach ($version->getApcCompetences() as $apcCompetence) {
                    if ($apcCompetence->isGoodParcours($apcParcour)) {

                        $this->excelWriter->writeCellXY('A', $ligne, $apcParcour->getLibelle());
                        $this->excelWriter->writeCellXY('B', $ligne, $apcCompetence->getNomCourt());
                        $this->excelWriter->writeCellXY('C', $ligne, $apcCompetence->getLibelle());

                        $ligneCompetence = $ligne;
                        //composantes essentielles
                        foreach ($apcCompetence->getApcComposanteEssentielles() as $composanteEssentielle) {
                            $this->excelWriter->writeCellXY('D', $ligne, $composanteEssentielle->display());
                            $ligne++;
                        }

                        $ligne = $ligneCompetence;
                        //situations professionnelles
                        foreach ($apcCompetence->getApcSituationProfessionnelles() as $composanteEssentielle) {
                            $this->excelWriter->writeCellXY('E', $ligne, $composanteEssentielle->getLibelle());
                            $ligne++;
                        }

                        $ligne = $ligneCompetence;
                        foreach ($tParcours[$apcParcour->getId()][$apcCompetence->getId()] as $niveau) {
                            foreach ($niveau->getApcApprentissageCritiques() as $apprentissageCritique) {
                                $this->excelWriter->writeCellXY('F', $ligne, $niveau->getLibelle());
                                $this->excelWriter->writeCellXY('G', $ligne, $apprentissageCritique->display());
                                $ligne++;
                            }
                        }
                    }
                }
            }

            $this->excelWriter->getColumnsAutoSizeInt(1, 10);
        }

        return $this->excelWriter->genereFichier('export_competences_tous_but');
    }
}
