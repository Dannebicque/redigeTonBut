<?php


namespace App\Classes\Apc;


use App\Classes\Excel\ExcelWriter;
use App\Entity\Departement;

class ApcApprentissageCritiqueExport
{
    private ExcelWriter $excelWriter;

    public function __construct(ExcelWriter $excelWriter)
    {
        $this->excelWriter = $excelWriter;
    }

    public function exportDepartement($acs, Departement $departement)
    {
        $this->excelWriter->nouveauFichier();

        foreach ($departement->getAnnees() as $annee) {
            $this->excelWriter->createSheet('BUT ' . $annee->getOrdre());
            $this->excelWriter->writeCellName('A1', 'Code');
            $this->excelWriter->writeCellName('B1', 'Libelle');
            $this->excelWriter->writeCellName('C1', 'Compétence');
            $this->excelWriter->writeCellName('D1', 'Parcours');

            $ligne = 2;
            /** @var \App\Entity\ApcApprentissageCritique $ac */
            foreach ($acs as $ac) {
                if ($ac->getNiveau()->getAnnee()->getId() === $annee->getId()) {
                    $this->excelWriter->writeCellXY(1, $ligne, $ac->getCode());
                    $this->excelWriter->writeCellXY(2, $ligne, $ac->getLibelle());
                    $this->excelWriter->writeCellXY(3, $ligne, $ac->getCompetence()?->getLibelle());
                    $col = 1;
                    foreach ($ac->getNiveau()->getApcParcoursNiveaux() as $parc) {
                        $this->excelWriter->writeCellXY(3+$col, $ligne, $parc->getParcours()->getLibelle());
                        $col++;
                    }
                    $ligne++;
                }
            }
        }
        return $this->excelWriter->genereFichier('Export_acs');
    }
}
