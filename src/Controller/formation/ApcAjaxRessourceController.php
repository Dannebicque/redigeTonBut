<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcRessourceController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 19/05/2021 16:40
 */

namespace App\Controller\formation;

use App\Classes\Matieres\RessourceManager;
use App\Classes\Pdf\MyPDF;
use App\Classes\Word\MyWord;
use App\Controller\BaseController;
use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcCompetence;
use App\Entity\ApcRessource;
use App\Entity\ApcRessourceApprentissageCritique;
use App\Entity\ApcRessourceCompetence;
use App\Entity\Departement;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcRessourceApprentissageCritiqueRepository;
use App\Repository\ApcRessourceCompetenceRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\ApcSaeRessourceRepository;
use App\Repository\SemestreRepository;
use App\Utils\Convert;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/formation/api/ressource", name="formation_")
 */
class ApcAjaxRessourceController extends BaseController
{
    /**
     * @Route("/ajax-ac", name="apc_ressources_ajax_ac", methods={"POST"}, options={"expose":true})
     */
    public function ajaxAc(
        SemestreRepository $semestreRepository,
        ApcRessourceApprentissageCritiqueRepository $apcRessourceApprentissageCritiqueRepository,
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        Request $request
    ): Response {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        $semestre = $semestreRepository->find($parametersAsArray['semestre']);
        $competences = $parametersAsArray['competences'];
        if (null !== $semestre && count($competences) > 0) {
            if (null !== $parametersAsArray['ressource']) {
                $tabAcSae = $apcRessourceApprentissageCritiqueRepository->findArrayIdAc($parametersAsArray['ressource']);
            } else {
                $tabAcSae = [];
            }

            $datas = $apcApprentissageCritiqueRepository->findBySemestreAndCompetences($semestre->getAnnee(),
                $competences);

            $t = [];
            $t['competences'] = [];
            foreach ($datas as $d) {
                $b = [];

                $b['id'] = $d->getId();
                $b['libelle'] = $d->getLibelle();
                $b['code'] = $d->getCode();
                $b['checked'] = true === in_array($d->getId(), $tabAcSae);

                if (null !== $d->getNiveau()) {
                    $key = $d->getNiveau()->getCompetence();
                    if ( null !== $key && !array_key_exists($key->getId(),
                            $t)) {
                        $t[$key->getId()] = [];
                    }

                    $t[$key->getId()][] = $b;
                    $t['competences'][$key->getId()] = '<span class="badge badge-'.$key->getCouleur().'">'.$key->getLibelle().'</span>';
                }
            }

            return $this->json($t);
        }

        return $this->json(false);
    }

    /**
     * @Route("/ajax-sae", name="apc_sae_ajax", methods={"POST"}, options={"expose":true})
     */
    public function ajaxSae(
        SemestreRepository $semestreRepository,
        ApcSaeRessourceRepository $apcSaeRessourceRepository,
        ApcSaeRepository $apcSaeRepository,
        Request $request
    ): Response {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        $semestre = $semestreRepository->find($parametersAsArray['semestre']);
        if (null !== $semestre) {
            if (null !== $parametersAsArray['ressource']) {
                $tabAcSae = $apcSaeRessourceRepository->findArrayIdSae($parametersAsArray['ressource']);
            } else {
                $tabAcSae = [];
            }

            $datas = $apcSaeRepository->findBySemestre($semestre);

            $t = [];
            foreach ($datas as $d) {
                $b = [];

                $b['id'] = $d->getId();
                $b['libelle'] = $d->getLibelle();
                $b['code'] = $d->getCodeMatiere();
                $b['checked'] = true === in_array($d->getId(), $tabAcSae);
                $t[] = $b;
            }

            return $this->json($t);
        }

        return $this->json(false);
    }

    /**
     * @Route("/ajax-prerequis", name="apc_prerequis_ajax", methods={"POST"}, options={"expose":true})
     */
    public function ajaxPrerequis(
        SemestreRepository $semestreRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Request $request
    ): Response {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        $semestre = $semestreRepository->find($parametersAsArray['semestre']);
        $tabPrerequis = [];
        $ressource = null;

        if (null !== $semestre) {
            if (null !== $parametersAsArray['ressource']) {
                $ressource = $apcRessourceRepository->find($parametersAsArray['ressource']);
                foreach ($ressource->getRessourcesPreRequises() as $rs) {
                    $tabPrerequis[] = $rs->getId();
                }
            }


            $datas = $apcRessourceRepository->findBySemestreEtPrecendent($semestre, $this->getDepartement()->getSemestres());

            $t = [];
            foreach ($datas as $d) {
                if ($ressource === null || $d->getId() !== $ressource->getId()) {
                    $b = [];


                    $b['id'] = $d->getId();
                    $b['libelle'] = $d->getLibelle();
                    $b['code'] = $d->getCodeMatiere();
                    $b['checked'] = true === in_array($d->getId(), $tabPrerequis);
                    $t[] = $b;
                }
            }

            return $this->json($t);
        }

        return $this->json(false);
    }

    /**
     * @Route("/ajax-parcours", name="apc_ressouce_parcours_ajax", methods={"POST"}, options={"expose":true})
     */
    public
    function ajaxParcours(
        SemestreRepository $semestreRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        Request $request
    ): Response {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        $semestre = $semestreRepository->find($parametersAsArray['semestre']);
        if (null !== $semestre && (($semestre->getOrdreLmd() > 2 && $this->getDepartement()->getTypeStructure() !== Departement::TYPE3) || $this->getDepartement()->getTypeStructure() === Departement::TYPE3)) {
            $datas = $this->getDepartement()->getApcParcours();
            if (count($datas) > 0) {
                if (null !== $parametersAsArray['ressource']) {
                    $tabRessourceParcours = $apcRessourceParcoursRepository->findArrayIdRessource($parametersAsArray['ressource']);
                } else {
                    $tabRessourceParcours = [];
                }

                $t = [];
                foreach ($datas as $d) {
                    $b = [];
                    $b['id'] = $d->getId();
                    $b['libelle'] = $d->getLibelle();
                    $b['code'] = $d->getCode();
                    $b['checked'] = true === in_array($d->getId(), $tabRessourceParcours);
                    $t[] = $b;
                }

                return $this->json($t);
            }
        }

        return $this->json(false);
    }

    /**
     * @Route("/{ressource}/{ac}/update_ajax", name="apc_ressource_ac_update_ajax", methods="POST",
     *                                         options={"expose":true})
     */
    public
    function updateAc(
        ApcRessourceApprentissageCritiqueRepository $apcRessourceApprentissageCritiqueRepository,
        Request $request,
        ApcRessource $ressource,
        ApcApprentissageCritique $ac
    ) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        //regarde si déjà existant
        $acRessource = $apcRessourceApprentissageCritiqueRepository->findOneBy([
            'ressource' => $ressource->getId(),
            'apprentissageCritique' => $ac->getId()
        ]);

        if ($acRessource !== null) {
            //selon la valeur, on supprime
            if ((bool)$parametersAsArray['value'] === false) {
                $this->entityManager->remove($acRessource);
            }
        } else {
            //selon la valeur, on ajoute
            if ((bool)$parametersAsArray['value'] === true) {
                $acRessource = new ApcRessourceApprentissageCritique($ressource, $ac);
                $this->entityManager->persist($acRessource);
            }
        }
        $this->entityManager->flush();

        return $this->json(true);
    }

    /**
     * @Route("/{ressource}/{competence}/update_coeff_ajax", name="apc_ressource_coeff_update_ajax", methods="POST",
     *                                                       options={"expose":true})
     */
    public
    function updateCoeff(
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        Request $request,
        ApcRessource $ressource,
        ApcCompetence $competence
    ) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        //regarde si déjà existant
        $acRessource = $apcRessourceCompetenceRepository->findOneBy([
            'ressource' => $ressource->getId(),
            'competence' => $competence->getId()
        ]);

        if ($acRessource !== null) {
            //on modifie
            $acRessource->setCoefficient(Convert::convertToFloat($parametersAsArray['valeur']));
        } else {
            //on ajoute
            $acRessource = new ApcRessourceCompetence($ressource, $competence);
            $acRessource->setCoefficient($parametersAsArray['valeur']);
            $this->entityManager->persist($acRessource);

        }
        $this->entityManager->flush();

        return $this->json(true);
    }

    /**
     * @Route("/{ressource}/{type}/update_heures_ajax", name="apc_ressource_heure_update_ajax", methods="POST",
     *                                                  options={"expose":true})
     */
    public
    function updateHeures(
        Request $request,
        ApcRessource $ressource,
        string $type
    ) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        switch ($type) {
            case 'heures_totales':
                $ressource->setHeuresTotales($parametersAsArray['valeur']);
                break;
            case 'heures_tp':
                $ressource->setTpPpn($parametersAsArray['valeur']);
                break;
        }
        $this->entityManager->flush();

        return $this->json(true);
    }
}
