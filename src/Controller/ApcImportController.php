<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller;

use App\Classes\Export\DepartementExport;
use App\Classes\Import\MyUpload;
use App\Classes\Import\ReferentielCompetenceImport;
use App\Entity\Constantes;
use App\Repository\ApcParcoursRepository;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route("/apc/import/referentiel", name:"administration_apc_referentiel_import_")]
class ApcImportController extends BaseController
{
    #[Route('/formation', name: 'formation_index')]
    public function importFormationIndex(
        Request $request,
        MyUpload $myUpload,
        ReferentielCompetenceImport $diplomeImport,
        ApcParcoursRepository $apcParcoursRepository
    ): Response {

        if ($request->isMethod('POST')) {
            if (null !== $this->getDepartement()) {
                $fichier = $myUpload->upload($request->files->get('fichier'), 'temp/', ['xlsx']);
                dump($fichier);
                $diplomeImport->import($this->getDepartement(), $fichier, 'formation');
                unlink($fichier);
                $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Maquette importée avec succès');
            }

        }

        return $this->render('apc_import/index.html.twig', [
            'parcours' => $apcParcoursRepository->findBy(['departement' => $this->getDepartement()->getId()])
        ]);
    }
}
