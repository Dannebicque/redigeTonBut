<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcReferentielFormationController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 27/05/2021 20:54
 */

namespace App\Controller\formation;

use App\Classes\Apc\ApcCoefficient;
use App\Controller\BaseController;
use App\Entity\ApcCompetence;
use App\Entity\ApcRessourceCompetence;
use App\Entity\ApcSae;
use App\Entity\ApcSaeCompetence;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcRessourceCompetenceRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeCompetenceRepository;
use App\Repository\ApcSaeRepository;
use App\Utils\Convert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/referentiel-formation", name="formation_")
 */
class ApcReferentielFormationController extends BaseController
{
    /**
     * @Route("/grille/{departement}", name="apc_referentiel_formation_grille", methods="GET")
     */
    public function grille(Departement $departement)
    {
        return $this->render('formation/referentiel-formation/grille.html.twig',
            [
                'departement' => $departement,
            ]);
    }

    public function grilleCoefficientsSemestre(
        ApcCoefficient $apcCoefficient,
        ApcNiveauRepository $apcNiveauRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Semestre $semestre
    ) {
        $saes = $apcSaeRepository->findBySemestre($semestre);
        $ressources = $apcRessourceRepository->findBySemestre($semestre);

        return $this->render('formation/referentiel-formation/_grilleCoefficientsSemestre.html.twig',
            [
                'semestre' => $semestre,
                'niveaux' => $apcNiveauRepository->findBySemestre($semestre),
                'saes' => $saes,
                'ressources' => $ressources,
                'coefficients' => $apcCoefficient->calculsCoefficients($saes, $ressources),
            ]);
    }


    /**
     * @Route("/ajax-edit/{id}/{competence}/{type}", name="apc_referentiel_formation_ajax", methods={"POST"},
     *                                               options={"expose":true})
     */
    public function ajaxEdit(
        ApcSaeCompetenceRepository $apcSaeCompetenceRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository,
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        Request $request,
        int $id,
        ApcCompetence $competence,
        string $type
    ): Response {
        $value = $request->request->get('value');

        if ('ressource' === $type) {
            $ressource = $apcRessourceRepository->find($id);
            if (null !== $ressource) {
                $obj = $apcRessourceCompetenceRepository->findOneBy([
                    'ressource' => $id,
                    'competence' => $competence->getId(),
                ]);
                if (null === $obj) {
                    $obj = new ApcRessourceCompetence($ressource, $competence);
                    $obj->setCoefficient(Convert::convertToFloat($value));
                    $this->entityManager->persist($obj);
                } else {
                    $obj->setCoefficient(Convert::convertToFloat($value));
                }
                $this->entityManager->flush();
            } else {
                $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'Erreur lors de la modification du coefficient');

                return new JsonResponse(false, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Coefficient modifié');

            return new JsonResponse(true, Response::HTTP_OK);
        }

        if ('sae' === $type) {
            $sae = $apcSaeRepository->find($id);
            if (null !== $sae) {
                $obj = $apcSaeCompetenceRepository->findOneBy(['sae' => $id, 'competence' => $competence->getId()]);
                if (null === $obj) {
                    $obj = new ApcSaeCompetence($sae, $competence);
                    $obj->setCoefficient(Convert::convertToFloat($value));
                    $this->entityManager->persist($obj);
                } else {
                    $obj->setCoefficient(Convert::convertToFloat($value));
                }
                $this->entityManager->flush();
            } else {
                $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'Erreur lors de la modification du coefficient');

                return new JsonResponse(false, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Coefficient modifié');

            return new JsonResponse(true, Response::HTTP_OK);
        }
        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'Type inexistant');

        return new JsonResponse(false, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
