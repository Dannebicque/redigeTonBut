<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcParcoursController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller\competences;

use App\Classes\Apc\ApcStructure;
use App\Controller\BaseController;
use App\Entity\ApcParcours;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Form\ApcParcoursType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/apc/parcours", name="administration_")
 */
class ApcParcoursController extends BaseController
{
    /**
     * @Route("/{departement}/new", name="apc_parcours_new", methods={"GET","POST"})
     */
    public function new(Request $request, Departement $departement): Response
    {
        $apcParcour = new ApcParcours($departement);
        $form = $this->createForm(ApcParcoursType::class, $apcParcour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($apcParcour);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'apcParcours.create.success.flash');

            return $this->redirectToRoute('administration_apc_parcours_show', ['id' => $apcParcour->getId()]);
        }

        return $this->render('competences/apc_parcours/new.html.twig', [
            'apc_parcour' => $apcParcour,
            'form'        => $form->createView(),
            'departement'     => $departement,
        ]);
    }

    /**
     * @Route("/{id}", name="apc_parcours_show", methods={"GET"})
     */
    public function show(ApcStructure $apcStructure, ApcParcours $apcParcour): Response
    {
        return $this->render('competences/apc_parcours/show.html.twig', [
            'parcour'         => $apcParcour,
            'parcoursNiveaux' => $apcStructure->parcoursNiveaux($apcParcour->getDepartement()),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="apc_parcours_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ApcParcours $apcParcour): Response
    {
        $form = $this->createForm(ApcParcoursType::class, $apcParcour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'apcParcours.edit.success.flash');

            return $this->redirectToRoute('administration_apc_parcours_show', ['id' => $apcParcour->getId()]);
        }

        return $this->render('competences/apc_parcours/edit.html.twig', [
            'apc_parcour' => $apcParcour,
            'form'        => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="apc_parcours_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ApcParcours $apcParcour): Response
    {
        $departement = $apcParcour->getDepartement();
        if ($this->isCsrfTokenValid('delete' . $apcParcour->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($apcParcour);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'apcParcours.delete.success.flash');
        }
        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'apcParcours.delete.error.flash');

        return $this->redirectToRoute('administration_apc_referentiel_index', ['departement' => $departement->getId()]);
    }

    /**
     * @Route("/{id}/duplicate", name="apc_parcours_duplicate", methods="GET|POST")
     */
    public function duplicate(ApcParcours $apcParcours): Response
    {
        $newApcParcours = clone $apcParcours;

        $this->entityManager->persist($newApcParcours);
        $this->entityManager->flush();
        $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'apcParcours.duplicate.success.flash');

        return $this->redirectToRoute('administration_apc_parcours_show', ['id' => $newApcParcours->getId()]);
    }
}
