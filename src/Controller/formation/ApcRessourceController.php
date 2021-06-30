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
use App\Entity\ApcSaeRessource;
use App\Entity\Constantes;
use App\Form\ApcRessourceType;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcRessourceApprentissageCritiqueRepository;
use App\Repository\ApcRessourceCompetenceRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\ApcSaeRessourceRepository;
use App\Repository\SemestreRepository;
use App\Utils\Convert;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/formation/ressource", name="formation_")
 */
class ApcRessourceController extends BaseController
{
//    /**
//     * @Route("/imprime/{id}.docx", name="apc_ressource_export_one_word", methods="GET")
//     */
//    public function exportWordOne(MyWord $myWord, ApcRessource $apcRessource): StreamedResponse
//    {
//        return $myWord->exportRessource($apcRessource);
//    }

//    /**
//     * @Route("/imprime/{id}.pdf", name="apc_ressource_export_one", methods="GET")
//     */
//    public function exportOne(MyPDF $myPDF, ApcRessource $apcRessource): PdfResponse
//    {
//        return $myPDF::generePdf(
//            'pdf/apc/fiche_ressource.html.twig',
//            ['apc_sae' => $apcRessource],
//            'ressource',
//            $this->getDepartement()
//        );
//    }


    /**
     * @Route("/ajax-ac", name="apc_ressources_ajax_ac", methods={"POST"}, options={"expose":true})
     */
    public function ajaxAc(
        SemestreRepository $semestreRepository,
        ApcRessourceApprentissageCritiqueRepository $apcRessourceApprentissageCritiqueRepository,
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        Request $request
    ): Response {
        $semestre = $semestreRepository->find($request->request->get('semestre'));
        $competences = $request->request->get('competences');
        if (null !== $semestre && count($competences) > 0) {
            if (null !== $request->request->get('ressource')) {
                $tabAcSae = $apcRessourceApprentissageCritiqueRepository->findArrayIdAc($request->request->get('ressource'));
            } else {
                $tabAcSae = [];
            }

            $datas = $apcApprentissageCritiqueRepository->findBySemestreAndCompetences($semestre->getAnnee(),
                $competences);

            $t = [];
            foreach ($datas as $d) {
                $b = [];

                $b['id'] = $d->getId();
                $b['libelle'] = $d->getLibelle();
                $b['code'] = $d->getCode();
                $b['checked'] = true === in_array($d->getId(), $tabAcSae) ? 'checked="checked"' : '';
                if (null !== $d->getNiveau() && null !== $d->getNiveau()->getCompetence() && !array_key_exists($d->getNiveau()->getCompetence()->getNomCourt(),
                        $t)) {
                    $t[$d->getNiveau()->getCompetence()->getNomCourt()] = [];
                }
                $t[$d->getNiveau()->getCompetence()->getNomCourt()][] = $b;
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
        $semestre = $semestreRepository->find($request->request->get('semestre'));
        if (null !== $semestre) {
            if (null !== $request->request->get('ressource')) {
                $tabAcSae = $apcSaeRessourceRepository->findArrayIdSae($request->request->get('ressource'));
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
                $b['checked'] = true === in_array($d->getId(), $tabAcSae) ? 'checked="checked"' : '';
                $t[] = $b;
            }

            return $this->json($t);
        }

        return $this->json(false);
    }

    /**
     * @Route("/new/", name="apc_ressource_new", methods={"GET","POST"})
     */
    public function new(
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        ApcSaeRepository $apcSaeRepository,
        Request $request
    ): Response {
        $apcRessource = new ApcRessource();
        $form = $this->createForm(ApcRessourceType::class, $apcRessource, [
            'departement' => $this->getDepartement(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($apcRessource);

            $acs = $request->request->get('ac');
            if (is_array($acs)) {
                foreach ($acs as $idAc) {
                    $ac = $apcApprentissageCritiqueRepository->find($idAc);
                    $saeAc = new ApcRessourceApprentissageCritique($apcRessource, $ac);
                    $this->entityManager->persist($saeAc);
                }
            }

            $saes = $request->request->get('saes');
            if (is_array($saes)) {
                foreach ($saes as $idAc) {
                    $apcSae = $apcSaeRepository->find($idAc);
                    $saeRes = new ApcSaeRessource($apcSae, $apcRessource);
                    $this->entityManager->persist($saeRes);
                }
            }

            $this->entityManager->flush();
            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'apc.ressource.new.success.flash'
            );

            return $this->redirectToRoute('but_ressources_annee', ['annee' => $apcRessource->getSemestre()->getAnnee()->getId()]);
        }

        return $this->render('formation/apc_ressource/new.html.twig', [
            'apc_ressource' => $apcRessource,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="apc_ressource_edit", methods={"GET","POST"})
     */
    public function edit(
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        ApcSaeRepository $apcSaeRepository,
        Request $request,
        ApcRessource $apcRessource
    ): Response {
        $form = $this->createForm(ApcRessourceType::class, $apcRessource, [
            'departement' => $this->getDepartement(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($apcRessource->getApcRessourceApprentissageCritiques() as $ac) {
                $this->entityManager->remove($ac);
            }

            $acs = $request->request->get('ac');
            if (is_array($acs)) {
                foreach ($acs as $idAc) {
                    $ac = $apcApprentissageCritiqueRepository->find($idAc);
                    $saeAc = new ApcRessourceApprentissageCritique($apcRessource, $ac);
                    $this->entityManager->persist($saeAc);
                }
            }

            foreach ($apcRessource->getApcSaeRessources() as $ac) {
                $this->entityManager->remove($ac);
            }
            $saes = $request->request->get('saes');
            if (is_array($saes)) {
                foreach ($saes as $idAc) {
                    $apcSae = $apcSaeRepository->find($idAc);
                    $saeRes = new ApcSaeRessource($apcSae, $apcRessource);
                    $this->entityManager->persist($saeRes);
                }
            }

            $this->entityManager->flush();

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'apc.ressource.edit.success.flash'
            );

            if (null !== $request->request->get('btn_update') && null !== $apcRessource->getSemestre() && null !== $apcRessource->getSemestre()->getAnnee()) {
                return $this->redirectToRoute('but_ressources_annee', ['annee' => $apcRessource->getSemestre()->getAnnee()->getId()]);

            }

            return $this->redirectToRoute('formation_apc_ressource_edit',
                ['id' => $apcRessource->getId()]);
        }

        return $this->render('formation/apc_ressource/edit.html.twig', [
            'apc_ressource' => $apcRessource,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="apc_ressource_delete", methods="DELETE")
     */
    public function delete(Request $request, ApcRessource $apcRessource): Response
    {
        $id = $apcRessource->getId();
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $this->entityManager->remove($apcRessource);
            $this->entityManager->flush();
            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'apc.ressource.delete.success.flash'
            );

            return $this->json($id, Response::HTTP_OK);
        }

        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'apc.ressource.delete.error.flash');

        return $this->json(false, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @Route("/{id}/duplicate", name="apc_ressource_duplicate", methods="GET|POST")
     */
    public function duplicate(ApcRessource $apcRessource): Response
    {
        $newApcRessource = clone $apcRessource;

        $this->entityManager->persist($newApcRessource);
        $this->entityManager->flush();
        $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'apc.ressource.duplicate.success.flash');

        return $this->redirectToRoute('formation_apc_ressource_edit', ['id' => $newApcRessource->getId()]);
    }

    /**
     * @Route("/{ressource}/{ac}/update_ajax", name="apc_ressource_ac_update_ajax", methods="POST", options={"expose":true})
     */
    public function updateAc(
        ApcRessourceApprentissageCritiqueRepository $apcRessourceApprentissageCritiqueRepository,
        Request $request, ApcRessource $ressource, ApcApprentissageCritique $ac) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        //regarde si déjà existant
        $acRessource = $apcRessourceApprentissageCritiqueRepository->findOneBy(['ressource' => $ressource->getId(), 'apprentissageCritique' => $ac->getId()]);

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
     * @Route("/{ressource}/{competence}/update_coeff_ajax", name="apc_ressource_coeff_update_ajax", methods="POST", options={"expose":true})
     */
    public function updateCoeff(
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        Request $request, ApcRessource $ressource, ApcCompetence $competence) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        //regarde si déjà existant
        $acRessource = $apcRessourceCompetenceRepository->findOneBy(['ressource' => $ressource->getId(), 'competence' => $competence->getId()]);

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
     * @Route("/{ressource}/{type}/update_heures_ajax", name="apc_ressource_heure_update_ajax", methods="POST", options={"expose":true})
     */
    public function updateHeures(
        Request $request, ApcRessource $ressource, string $type) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        switch ($type)
        {
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
