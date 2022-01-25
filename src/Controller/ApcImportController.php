<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller;

use App\Classes\Import\MyUpload;
use App\Classes\Import\ReferentielCompetenceImport;
use App\Entity\Constantes;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/apc/import/referentiel", name: "administration_apc_referentiel_import_")]
class ApcImportController extends BaseController
{
//    #[Route('/formation', name: 'formation_index')]
//    public function importFormationIndex(
//        ApcRessourceRepository $apcRessourceRepository,
//        ApcSaeRepository $apcSaeRepository,
//        Request $request,
//        MyUpload $myUpload,
//        ReferentielCompetenceImport $diplomeImport,
//        ApcParcoursRepository $apcParcoursRepository
//    ): Response {
//
//        if ($request->isMethod('POST')) {
//            if (null !== $this->getDepartement()) {
//                    //effacer
//                    $ressources = $apcRessourceRepository->findByDepartement($this->getDepartement());
//                    foreach ($ressources as $res) {
//                        foreach ($res->getApcRessourceApprentissageCritiques() as $as) {
//                            $this->entityManager->remove($as);
//                        }
//                        foreach ($res->getApcRessourceCompetences() as $as) {
//                            $this->entityManager->remove($as);
//                        }
//                        foreach ($res->getApcRessourceParcours() as $as) {
//                            $this->entityManager->remove($as);
//                        }
//                        foreach ($res->getApcSaeRessources() as $as) {
//                            $this->entityManager->remove($as);
//                        }
//                        foreach ($res->getRessourcesPreRequises() as $as) {
//                            $as->removeRessourcesPreRequise($res);
//                            $res->removeApcRessource($as);
//                        }
//
//                        $this->entityManager->remove($res);
//                    }
//                    $saes = $apcSaeRepository->findByDepartement($this->getDepartement());
//                    foreach ($saes as $sae) {
//                        foreach ($sae->getApcSaeApprentissageCritiques() as $as) {
//                            $this->entityManager->remove($as);
//                        }
//                        foreach ($sae->getApcSaeRessources() as $as) {
//                            $this->entityManager->remove($as);
//                        }
//                        foreach ($sae->getApcSaeCompetences() as $as) {
//                            $this->entityManager->remove($as);
//                        }
//                        foreach ($sae->getApcSaeParcours() as $as) {
//                            $this->entityManager->remove($as);
//                        }
//                        $this->entityManager->remove($sae);
//                    }
//                    $this->entityManager->flush();
//
//                $fichier = $myUpload->upload($request->files->get('fichier'), 'temp/', ['xlsx', 'xml']);
//                $diplomeImport->import($this->getDepartement(), $fichier, 'formation');
//                unlink($fichier);
//                $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Maquette importée avec succès');
//            }
//
//        }
//
//        return $this->render('apc_import/index.html.twig', [
//            'parcours' => $apcParcoursRepository->findBy(['departement' => $this->getDepartement()->getId()])
//        ]);
//    }
}
