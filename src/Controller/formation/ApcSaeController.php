<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcSaeController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 19/05/2021 15:55
 */

namespace App\Controller\formation;


use App\Classes\Apc\ApcSaeAddEdit;
use App\Classes\Apc\ApcSaeOrdre;
use App\Controller\BaseController;
use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Entity\ApcSaeParcours;
use App\Entity\Constantes;
use App\Entity\Semestre;
use App\Form\ApcSaeType;
use App\Repository\ApcParcoursRepository;
use App\Utils\Codification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/formation/sae", name="formation_")
 */
class ApcSaeController extends BaseController
{
    #[Route('/new/{semestre}/{parcours}', name: 'apc_sae_new', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function new(
        ApcSaeOrdre $apcSaeOrdre,
        ApcSaeAddEdit $apcSaeAddEdit,
        Request $request,
        Semestre $semestre = null,
        ApcParcours $parcours = null
    ): Response {
        $this->denyAccessUnlessGranted('new', $semestre ?? $this->getDepartement());
        $apcSae = new ApcSae();

        if ($semestre !== null) {
            $apcSae->setSemestre($semestre);
            $apcSae->setOrdre($apcSaeOrdre->getOrdreSuivant($semestre));
        }

        $form = $this->createForm(ApcSaeType::class, $apcSae, [
            'departement' => $this->getDepartement(),
            'editable' => $this->isGranted('ROLE_GT'),
            'parcours' => $parcours
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcSaeAddEdit->addOrEdit($apcSae, $request);

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'SAÉ ajoutée avec succès.'
            );

            if ($parcours !== null) {
                return $this->redirectToRoute('but_sae_annee',
                    ['annee' => $apcSae->getSemestre()->getAnnee()->getId(), 'semestre' => $apcSae->getSemestre()->getId(), 'parcours' => $parcours->getId() ]);
            }

            return $this->redirectToRoute('but_sae_annee', ['annee' => $apcSae->getSemestre()->getAnnee()->getId(), 'semestre' => $apcSae->getSemestre()->getId()]);
        }

        return $this->render('formation/apc_sae/new.html.twig', [
            'apc_sae' => $apcSae,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="apc_sae_edit", methods={"GET","POST"})
     */
    public function edit(
        ApcParcoursRepository $apcParcoursRepository,
        ApcSaeAddEdit $apcSaeAddEdit,
        Request $request,
        ApcSae $apcSae
    ): Response {
        $this->denyAccessUnlessGranted('edit', $apcSae);

        $parc = $request->query->get('parcours');
        $parcours= null;
        if ($parc !== null) {
            $parcours = $apcParcoursRepository->find($parc);
        }

        $form = $this->createForm(ApcSaeType::class, $apcSae, [
            'departement' => $this->getDepartement(),
            'editable' => $this->isGranted('ROLE_GT'),
            'parcours' => $parcours
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $apcSaeAddEdit->removeLiens($apcSae);
            $apcSaeAddEdit->addOrEdit($apcSae, $request);

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'SAÉ modifiée avec succès.'
            );

            if (null !== $request->request->get('btn_update') && null !== $apcSae->getSemestre() && null !== $apcSae->getSemestre()->getAnnee()) {
                if ($parcours === null) {
                    return $this->redirectToRoute('but_sae_annee', [
                        'annee' => $apcSae->getSemestre()->getAnnee()->getId(),
                        'semestre' => $apcSae->getSemestre()->getId(),
                    ]);
                }

                return $this->redirectToRoute('but_sae_annee', [
                    'annee' => $apcSae->getSemestre()->getAnnee()->getId(),
                    'semestre' => $apcSae->getSemestre()->getId(),
                    'parcours' => $parcours->getId()
                ]);
            }

            if ($parcours === null) {
                return $this->redirectToRoute('formation_apc_sae_edit',
                    ['id' => $apcSae->getId()]);
            }
            return $this->redirectToRoute('formation_apc_sae_edit',
                ['id' => $apcSae->getId(), 'parcours' => $parcours->getId()]);
        }

        if ($parcours === null) {
            return $this->render('formation/apc_sae/edit.html.twig', [
                'apc_sae' => $apcSae,
                'form' => $form->createView()
            ]);
        }
        return $this->render('formation/apc_sae/edit.html.twig', [
            'apc_sae' => $apcSae,
            'form' => $form->createView(),
            'parcours' => $parcours->getId()
        ]);
    }

    /**
     * @Route("/{id}/effacer", name="apc_sae_delete", methods={"POST"})
     */
    public function delete(Request $request, ApcSae $apcSae): Response
    {
        $this->denyAccessUnlessGranted('delete', $apcSae);
        $id = $apcSae->getId();
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $semestre = $apcSae->getSemestre();
            $this->entityManager->remove($apcSae);
            $this->entityManager->flush();
            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'SAÉ supprimée avec succès.'
            );

            return $this->redirectToRoute('but_sae_annee', ['annee' => $semestre->getAnnee()->getId(), 'semestre' => $semestre->getId(), 'parcours' => $request->query->get('parcours') ]);
        }

        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'Erreur lors de la suppression de la SAÉ.');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/{id}/duplicate", name="apc_sae_duplicate", methods="GET|POST")
     */
    public function duplicate(
        ApcSaeAddEdit $addEdit,
        ApcSae $apcSae): Response
    {
        $this->denyAccessUnlessGranted('edit', $apcSae);
        $newApcSae = $addEdit->duplique($apcSae);
        $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'SAÉ dupliquée avec succès.');

        return $this->redirectToRoute('formation_apc_sae_edit', ['id' => $newApcSae->getId()]);
    }

    /**
     * @Route("/{id}/deplace/{position}", name="apc_sae_deplace", methods="GET")
     */
    public function deplace(
        Request $request,
        ApcSaeOrdre $apcSaeOrdre,
        ApcSae $apcSae, int $position): Response
    {
        $apcSaeOrdre->deplaceSae($apcSae, $position);

        return $this->redirect($request->headers->get('referer'));

    }
}
