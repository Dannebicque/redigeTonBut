<?php

namespace App\Classes\Export;

use App\Classes\Apc\ApcStructure;
use App\Classes\JsonDiffService;
use App\Entity\Departement;
use App\Utils\Files;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Twig\Environment;

class CompetencesExport
{
    public function __construct(
        protected Environment $twig,
        private DepartementExport $departementExport, private ApcStructure $apcStructure, private Pdf $knpSnappyPdf, private Files $files
    ) {
    }

    public function generePdfVersionCompetences(?Departement $departement): PdfResponse
    {
        if (null === $departement) {
            throw new \Exception('Departement inconnu');
        }

        // version précédente :
        $fichier = $this->files->getLastVersionFile($departement);
        $tabAncien = json_decode(file_get_contents($fichier), true);

        // version courante :
        $tabActuel = $this->departementExport->genereJson($departement);

        $diffService = new JsonDiffService();
        $diffs = $diffService->compare($tabAncien, $tabActuel);

        $tParcours = $this->apcStructure->parcoursNiveaux($departement);
        $competences = $departement->getApcCompetences();
        $tComp = [];
        foreach ($competences as $comp) {
            $tComp[$comp->getId()] = $comp;
        }

        $competencesParcours = [];

        foreach ($tParcours as $key => $parc) {
            $competencesParcours[$key] = [];
            foreach ($parc as $k => $v) {
                $competencesParcours[$key][] = $tComp[$k];
            }
        }

        $html = $this->twig->render('competences/export-versionning-referentiel.html.twig', [
            'competencesParcours' => $competencesParcours,
            'departement' => $departement,
            'competences' => $competences,
            'parcours' => $departement->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
            'diffs' => $diffs,
        ]);

        return new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html, [
                'orientation' => 'Landscape'
            ]),
            'referentiel-competence-version-' . $departement->getSigle() . '.pdf'
        );
    }
}
