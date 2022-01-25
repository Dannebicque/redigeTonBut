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
use App\Classes\Import\MyUpload;
use App\Classes\Import\ReferentielCompetenceImport;
use App\Controller\BaseController;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Repository\DepartementRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/apc/referentiel-competences")]
class ApcController extends BaseController
{
    #[Route("/consulter/{departement}", name:"administration_apc_referentiel_index", methods:["GET"])]
    public function referentiel(ApcStructure $apcStructure, Departement $departement = null): Response
    {
        //todo: ordre compétence fonctionnel, mais pas dans l'affichage...
        $tParcours = $apcStructure->parcoursNiveaux($departement);
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

        return $this->render('competences/referentiel.html.twig', [
            'competencesParcours' => $competencesParcours,
            'departement' => $departement,
            'competences' => $competences,
            'parcours' => $departement->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
        ]);
    }

    #[Route('/exporter/{departement}', name: 'export_referentiel_competences', methods: ['GET'])]
    public function exportReferentiel(Pdf $knpSnappyPdf, ApcStructure $apcStructure, Departement $departement = null): PdfResponse
    {
        $tParcours = $apcStructure->parcoursNiveaux($departement);
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

        $html = $this->renderView('competences/export-referentiel.html.twig',[
            'competencesParcours' => $competencesParcours,
            'departement' => $departement,
            'competences' => $competences,
            'parcours' => $departement->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
        ]);

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html, [
                'orientation'=>'Landscape'
            ]),
            'referentiel-competence-'.$departement->getSigle().'.pdf'
        );
    }

//    #[Route("/import", name:"administration_apc_referentiel_import", methods:["GET","POST"])]
//    public function import(
//        DepartementRepository $departementRepository,
//        MyUpload $myUpload,
//        ReferentielCompetenceImport $diplomeImport,
//        Request $request
//    ): Response {
//        if ($request->isMethod('POST')) {
//            if (null !== $this->getDepartement()) {
//                $fichier = $myUpload->upload($request->files->get('fichier'), 'temp/', ['xml', 'xlsx']);
//                $diplomeImport->import($this->getDepartement(), $fichier, $request->request->get('typeFichier'));
//                unlink($fichier);
//                $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Maquette importée avec succès');
//            }
//
//        }
//
//        return $this->render('import_referentiel/index.html.twig', [
//            'departements' => $departementRepository->findAll(),
//        ]);
//    }
}
