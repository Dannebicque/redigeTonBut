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
use App\Form\ApcParcoursType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/apc/parcours", name: "administration_")]
class ApcParcoursController extends BaseController
{
    // ajouter un parcours de spécialité
    #[Route("/new", name: "apc_parcours_new", methods: ["GET", "POST"])]
    public function new(
        Request $request,
    ): Response {
        if ($this->getDepartement() === null) {
           return $this->redirectToRoute('homepage');
        }

        $apcParcour = new ApcParcours($this->getDepartement());
        $form = $this->createForm(ApcParcoursType::class, $apcParcour, [
            'referentiel_bloque' => $this->getDepartement()->getVerouilleCompetences()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($apcParcour);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Parcours de spécialité créé avec succès.');

            return $this->redirectToRoute('administration_apc_referentiel_index', [
                'departement' => $apcParcour->getDepartement()?->getId()
            ]);
        }

        return $this->render('competences/apc_parcours/new.html.twig', [
            'apc_parcour' => $apcParcour,
            'form' => $form->createView(),
        ]);
    }


    #[Route("/{id}/edit", name: "apc_parcours_edit", methods: ["GET", "POST"])]
    public function edit(
        Request $request,
        ApcParcours $apcParcour
    ): Response {
        $form = $this->createForm(ApcParcoursType::class, $apcParcour, [
            'referentiel_bloque' => $this->getDepartement()?->getVerouilleCompetences()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Parcours de spécialité modifié avec succès.');
        }

        return $this->render('competences/apc_parcours/edit.html.twig', [
            'apc_parcour' => $apcParcour,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'apc_parcours_show', methods: ['GET'])]
    public function show(
        ApcParcours $apcParcours
    ): Response {
        return $this->render('competences/apc_parcours/show.html.twig', [
            'apcParcours' => $apcParcours,
        ]);
    }

    #[Route('/{id}/delete', name: 'apc_parcours_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ApcParcours $apcParcours
    ): Response {

        $this->denyAccessUnlessGranted('COMPETENCES_DELETE', $apcParcours);

        if ($this->isCsrfTokenValid('delete'.$apcParcours->getId(), $request->request->get('_token'))) {

            $departement = $apcParcours->getDepartement();
            if ($departement === null) {
                throw $this->createNotFoundException('Departement introuvable');
            }

            if ($departement->getVerouilleCompetences() === true) {
                $this->addFlashBag('warning', 'Le référentiel est verrouillé, vous ne pouvez pas supprimer un parcours');
                return $this->redirectToRoute('administration_apc_referentiel_index', ['departement' => $departement->getId()]);
            }

            foreach ($apcParcours->getIutSiteParcours() as $iutSiteParcours) {
                $this->entityManager->remove($iutSiteParcours);
            }

            foreach ($apcParcours->getApcParcoursNiveaux() as $apcParcoursNiveau) {
                $this->entityManager->remove($apcParcoursNiveau);
            }

            foreach ($apcParcours->getApcSaeParcours() as $apcSaeParcours) {
                $this->entityManager->remove($apcSaeParcours);
            }

            foreach ($apcParcours->getApcRessourceParcours() as $apcRessourceParcours) {
                $this->entityManager->remove($apcRessourceParcours);
            }


            $this->entityManager->remove($apcParcours);
            $this->entityManager->flush();

            $this->addFlashBag('success', 'Parcours supprimé, avec l\'ensemble des liens vers les ressources, SAE et compétences');

            return $this->redirectToRoute('administration_apc_referentiel_index', ['departement' => $departement->getId()]);
        }
    }
}
