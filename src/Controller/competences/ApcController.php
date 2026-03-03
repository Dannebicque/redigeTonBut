<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller\competences;

use App\Classes\Apc\ApcStructure;
use App\Classes\Export\CompetencesExport;
use App\Classes\Export\DepartementExport;
use App\Classes\Import\MyUpload;
use App\Classes\Import\ReferentielCompetenceImport;
use App\Controller\BaseController;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Entity\Version;
use App\Repository\DepartementRepository;
use App\Classes\JsonDiffService;
use App\Repository\VersionRepository;
use App\Utils\Files;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/apc/referentiel-competences")]
class ApcController extends BaseController
{
    #[Route("/consulter/{version}", name:"administration_apc_referentiel_index", methods:["GET"])]
    public function referentiel(
        ApcStructure $apcStructure, Version $version = null): Response
    {
        if (null === $version) {
            throw new \Exception('Departement inconnu');
        }


        $tParcours = $apcStructure->parcoursNiveaux($version);
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


        return $this->render('competences/referentiel.html.twig', [
            'competencesParcours' => $competencesParcours,
            'departement' => $version->getDepartement(),
            'version' => $version,
            'competences' => $competences,
            'parcours' => $version->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
        ]);
    }

    #[Route('/exporter/{version}', name: 'export_referentiel_competences', methods: ['GET'])]
    public function exportReferentiel(Pdf $knpSnappyPdf, ApcStructure $apcStructure, Version $version = null): PdfResponse
    {
        if (null === $version) {
            throw new \Exception('Departement inconnu');
        }

        $tParcours = $apcStructure->parcoursNiveaux($version);
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

        $html = $this->renderView('competences/export-referentiel.html.twig',[
            'competencesParcours' => $competencesParcours,
            'departement' => $version->getDepartement(),
            'competences' => $competences,
            'parcours' => $version->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
        ]);

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html, [
                'orientation'=>'Landscape'
            ]),
            'referentiel-competence-'.$version->getDepartement()->getSigle().'.pdf'
        );
    }

    #[Route('/exporter-versionning/{version}', name: 'export_versionning_referentiel_competences', methods: ['GET'])]
    public function exportVersionReferentiel(
        CompetencesExport $competencesExport,
        Version $version = null): Response //PdfResponse
    {
        return $competencesExport->generePdfVersionCompetences($version);
    }

    #[Route('/voir-versionning/{version}', name: 'voir_versionning_referentiel_competences', methods: ['GET'])]
    public function voirVersionReferentiel(
        Files $files,
        DepartementExport $departementExport,
        ApcStructure $apcStructure, Version $version = null): Response //PdfResponse
    {
        if (null === $version) {
            throw new \Exception('Departement inconnu');
        }
//todo: A REFAIRE...
        // version précédente :
        $fichier = $files->getLastVersionFile($version->getDepartement());
        $tabAncien = json_decode(file_get_contents($fichier), true);
        $departement = $version->getDepartement();
        // version courante :
        $tabActuel = $departementExport->genereJson($departement);

        $diffService = new JsonDiffService();
        $diffs = $diffService->compare($tabAncien, $tabActuel);
        $tParcours = $apcStructure->parcoursNiveaux($version);
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

        return  $this->render('competences/voir-versionning-referentiel.html.twig',[
            'competencesParcours' => $competencesParcours,
            'departement' => $departement,
            'competences' => $competences,
            'parcours' => $departement->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
            'diffs' => $diffs,
        ]);
    }

    #[Route("/import", name:"administration_apc_referentiel_import", methods:["GET","POST"])]
    public function import(
        DepartementRepository $departementRepository,
        MyUpload $myUpload,
        ReferentielCompetenceImport $diplomeImport,
        Request $request
    ): Response {
        if ($request->isMethod('POST') && null !== $this->getDepartement()) {
            $fichier = $myUpload->upload($request->files->get('fichier'), 'temp/', ['xml', 'xlsx']);
            $diplomeImport->import($this->getDepartement(), $fichier, $request->request->get('typeFichier'));
            unlink($fichier);
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Maquette importée avec succès');
        }

        return $this->render('import_referentiel/index.html.twig', [
            'departements' => $departementRepository->findAll(),
        ]);
    }
}
