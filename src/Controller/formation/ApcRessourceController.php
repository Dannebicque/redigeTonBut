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
use App\Entity\ApcRessource;
use App\Entity\ApcRessourceApprentissageCritique;
use App\Entity\ApcSaeRessource;
use App\Entity\Constantes;
use App\Entity\Semestre;
use App\Form\ApcRessourceType;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcSaeRepository;
use App\Utils\Codification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/formation/ressource", name="formation_")
 */
class ApcRessourceController extends BaseController
{
    /**
     * @Route("/new/{semestre}", name="apc_ressource_new", methods={"GET","POST"})
     */
    public function new(
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        ApcSaeRepository $apcSaeRepository,
        Request $request,
        Semestre $semestre = null
    ): Response {
        $apcRessource = new ApcRessource();

        if ($semestre !== null) {
            $apcRessource->setSemestre($semestre);
        }

        $form = $this->createForm(ApcRessourceType::class, $apcRessource, [
            'departement' => $this->getDepartement(),
            'editable' => $this->isGranted('ROLE_GT')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcRessource->setCodeMatiere(Codification::codeRessource($apcRessource));
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

            return $this->redirectToRoute('but_ressources_annee',
                ['annee' => $apcRessource->getSemestre()->getAnnee()->getId()]);
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
            'editable' => $this->isGranted('ROLE_GT')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcRessource->setCodeMatiere(Codification::codeRessource($apcRessource));

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
                return $this->redirectToRoute('but_ressources_annee',
                    ['annee' => $apcRessource->getSemestre()->getAnnee()->getId()]);

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
     * @Route("/{id}/deplace/{position}", name="apc_ressource_deplace", methods="GET")
     */
    public function deplace(Request $request, ApcRessource $apcRessource, int $position): Response
    {

    }
}
