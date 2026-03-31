<?php

namespace App\Classes\Export;

use App\Classes\Apc\ApcStructure;
use App\Classes\JsonDiffService;
use App\Entity\Departement;
use App\Entity\Version;
use App\Utils\Files;
use Exception;
//use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
//use Knp\Snappy\Pdf;
use Twig\Environment;

class CompetencesExport
{
    public function __construct(
        protected Environment              $twig,
        private readonly DepartementExport $departementExport, private readonly ApcStructure $apcStructure,
//        private readonly Pdf $knpSnappyPdf,
        private readonly Files $files
    ) {
    }

    public function generePdfVersionCompetences(?Version $version)
    {
        throw new Exception('Fonctionnalité temporairement indisponible');
        if (null === $version) {
            throw new Exception('Departement inconnu');
        }

        //todo: a refaire...

        $departement = $version->getDepartement();

        // version précédente :
        $fichier = $this->files->getLastVersionFile($departement);
        $tabAncien = json_decode(file_get_contents($fichier), true);

        // version courante :
        $tabActuel = $this->departementExport->genereJson($version);

        $diffService = new JsonDiffService();
        $diffs = $diffService->compare($tabAncien, $tabActuel);

        $tParcours = $this->apcStructure->parcoursNiveaux($version);
        $competences = $version->getApcCompetences();
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
            'parcours' => $version->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
            'diffs' => $diffs,
        ]);

//        return new PdfResponse(
//            $this->knpSnappyPdf->getOutputFromHtml($html, [
//                'orientation' => 'Landscape'
//            ]),
//            'referentiel-competence-version-' . $departement->getSigle() . '.pdf'
//        );
    }
}
