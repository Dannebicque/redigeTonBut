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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/apc/referentiel-competences")
 */
class ApcController extends BaseController
{
    /**
     * @Route("/consulter/{departement}", name="administration_apc_referentiel_index", methods={"GET"})
     */
    public function referentiel(ApcStructure $apcStructure, Departement $departement = null): Response
    {
        if ($departement === null) {
                //département de l'utilisateur N
        }

        $tParcours = $apcStructure->parcoursNiveaux($departement);

        return $this->render('competences/referentiel.html.twig', [
            'departement'         => $departement,
            'competences'     => $departement->getApcCompetences(),
            'parcours'        => $departement->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
        ]);
    }

    /**
     * @Route("/import", name="administration_apc_referentiel_import", methods={"GET|POST"})
     */
    public function index(
        DepartementRepository $departementRepository,
        MyUpload $myUpload,
        ReferentielCompetenceImport $diplomeImport,
        Request $request
    ): Response {
        if ($request->isMethod('POST')) {
            $departement = $departementRepository->find($request->request->get('departement'));
            if (null !== $departement) {
                $fichier = $myUpload->upload($request->files->get('fichier'), 'temp/', ['xml', 'xlsx']);
                $diplomeImport->import($departement, $fichier, $request->request->get('typeFichier'));
                unlink($fichier);
                $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Maquette importée avec succès');
            }

        }

        return $this->render('import_referentiel/index.html.twig', [
            'departements' => $departementRepository->findAll(),
        ]);
    }
}
