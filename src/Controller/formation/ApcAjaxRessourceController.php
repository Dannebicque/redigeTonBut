<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcRessourceController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 19/05/2021 16:40
 */

namespace App\Controller\formation;

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
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\ApcSaeRessourceRepository;
use App\Repository\SemestreRepository;
use App\Utils\Convert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/formation/api/ressource", name:"formation_")]
class ApcAjaxRessourceController extends BaseController
{
    #[Route("/ajax-ac", name: "apc_ressources_ajax_ac", options: ["expose" => true], methods: ["POST"])]
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
                $b['checked'] = in_array($d->getId(), $tabAcSae);

                if (null !== $d->getNiveau()) {
                    $key = $d->getNiveau()->getCompetence();
                    if ( null !== $key && !array_key_exists($key->getId(),
                            $t)) {
                        $t[$key->getId()] = [];
                    }

                    $t[$key->getId()][] = $b;
                }
            }

            return $this->json($t);
        }

        return $this->json(false);
    }

    #[Route("/ajax-sae", name: "apc_sae_ajax", options: ["expose" => true], methods: ["POST"])]
    public function ajaxSae(
        SemestreRepository $semestreRepository,
        ApcSaeRessourceRepository $apcSaeRessourceRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
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

            if ($semestre->getVersion()->getDepartement()->getTypeStructure() === Departement::TYPE3) {
                $parcours = $semestre->getApcParcours();
                if ($parcours !== null) {
                    $datas = $apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
                    //$datas = array_merge($datas, $datas2);
                }
            }

            $t = [];
            foreach ($datas as $d) {
                $b = [];

                $b['id'] = $d->getId();
                $b['libelle'] = $d->getLibelle();
                $b['code'] = $d->getCodeMatiere();
                $b['checked'] = in_array($d->getId(), $tabAcSae);
                $t[] = $b;
            }

            return $this->json($t);
        }

        return $this->json(false);
    }

    #[Route("/ajax-prerequis", name: "apc_prerequis_ajax", options: ["expose" => true], methods: ["POST"])]
    public function ajaxPrerequis(
        SemestreRepository $semestreRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
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

            if ($this->getDepartement()->getTypeStructure() === Departement::TYPE3) {
                    $parcours = $semestre->getApcParcours();
                    if ($parcours !== null) {
                        $datas = $apcRessourceParcoursRepository->findBySemestreEtPrecedent($semestre, $parcours, $this->getVersion()->getSemestres());
                    }
            } else {
                $datas = $apcRessourceRepository->findBySemestreEtPrecedent($semestre, $this->getVersion()->getSemestres());
            }

            $t = [];
            foreach ($datas as $d) {
                    if ($this->getDepartement()?->getTypeStructure() === Departement::TYPE3 && $d->getRessource() !== null) {
                        if ($ressource === null || $d->getRessource()->getId() !== $ressource->getId()) {
                            $b = [];
                            $b['id'] = $d->getRessource()->getId();
                            $b['libelle'] = $d->getRessource()->getLibelle();
                            $b['code'] = $d->getRessource()->getCodeMatiere();
                            $b['checked'] = in_array($d->getRessource()->getId(), $tabPrerequis);
                            $t[] = $b;
                        }
                    } elseif ($ressource === null || $d->getId() !== $ressource->getId()) {
                        $b = [];
                        $b['id'] = $d->getId();
                        $b['libelle'] = $d->getLibelle();
                        $b['code'] = $d->getCodeMatiere();
                        $b['checked'] = in_array($d->getId(), $tabPrerequis);
                        $t[] = $b;
                    }
            }

            return $this->json($t);
        }

        return $this->json(false);
    }

   #[Route("/ajax-parcours", name: "apc_ressouce_parcours_ajax", options: ["expose" => true], methods: ["POST"])]
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
            $datas = $this->getVersion()->getApcParcours();
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
                    $b['checked'] = in_array($d->getId(), $tabRessourceParcours);
                    $t[] = $b;
                }

                return $this->json($t);
            }
        }

        return $this->json(false);
    }

    #[Route("/{ressource}/{ac}/update_ajax", name: "apc_ressource_ac_update_ajax", options: ["expose" => true], methods: ["POST"])]
    public function updateAc(
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        ApcRessourceApprentissageCritiqueRepository $apcRessourceApprentissageCritiqueRepository,
        Request $request,
        ApcRessource $ressource,
        ApcApprentissageCritique $ac
    ): JsonResponse {
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

            //todo: vérifier si la compétence est associée et qu'il n'y a plus d'AC, donc supprimer ?
        } elseif ((bool)$parametersAsArray['value']) {
            //selon la valeur, on ajoute
            $acRessource = new ApcRessourceApprentissageCritique($ressource, $ac);
            $this->entityManager->persist($acRessource);
            //vérifier si la compétence est déjà associée dans le cas contraire, ajouter.
            $comp = $ac->getCompetence();
            if ($comp instanceof ApcCompetence) {
                $cp = $apcRessourceCompetenceRepository->findOneBy([
                    'competence' => $comp->getId(),
                    'ressource' => $ressource->getId()
                ]);
                if ($cp === null) {
                    $competence = new ApcRessourceCompetence($ressource,$comp);
                    $this->entityManager->persist($competence);
                }
            }
        }

        $this->entityManager->flush();

        return $this->json(true);
    }

    #[Route("/{ressource}/{competence}/update_coeff_ajax", name: "apc_ressource_coeff_update_ajax", options: ["expose" => true], methods: ["POST"])]
    public function updateCoeff(
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        Request $request,
        ApcRessource $ressource,
        ApcCompetence $competence
    ): JsonResponse {
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
            $acRessource->setCoefficient(Convert::convertToFloat($parametersAsArray['valeur']));
            $this->entityManager->persist($acRessource);

        }

        $this->entityManager->flush();

        return $this->json(true);
    }

    #[Route("/{ressource}/{type}/update_heures_ajax", name: "apc_ressource_heure_update_ajax", options: ["expose" => true], methods: ["POST"])]
    public
    function updateHeures(
        Request $request,
        ApcRessource $ressource,
        string $type
    ): JsonResponse {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        switch ($type) {
            case 'heures_totales':
                $ressource->setHeuresTotales(Convert::convertToFloat($parametersAsArray['valeur']));
                break;
            case 'heures_tp':
                $ressource->setTpPpn(Convert::convertToFloat($parametersAsArray['valeur']));
                break;
        }

        $this->entityManager->flush();

        return $this->json(true);
    }
}
