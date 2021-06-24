<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcCompetenceController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 01/06/2021 19:12
 */

namespace App\Controller\competences;

use App\Controller\BaseController;
use App\Entity\ApcCompetence;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Form\ApcCompetenceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/apc/competence")]
class ApcCompetenceController extends BaseController
{


    #[Route("/{departement}/new", name:"administration_apc_competence_new", methods:["GET","POST"])]
    public function new(Request $request, Departement $departement): Response
    {
        $apcComptence = new ApcCompetence($departement);
        $form = $this->createForm(ApcCompetenceType::class, $apcComptence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($apcComptence);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'apc.competence.create.success.flash');

            return $this->redirectToRoute('administration_apc_referentiel_index', ['diplome' => $departement->getId()]);
        }

        return $this->render('competences/apc_competence/new.html.twig', [
            'apc_competence' => $apcComptence,
            'form' => $form->createView(),
            'departement' => $departement,
        ]);
    }

     #[Route("/{id}/detail", name:"administration_apc_competence_show", methods:["GET"])]
    public function show(ApcCompetence $apcCompetence): Response
    {
        return $this->render('competences/apc_competence/show.html.twig', [
            'competence' => $apcCompetence,
        ]);
    }

     #[Route("/{id}/edit", name:"administration_apc_competence_edit", methods:["GET","POST"])]
    public function edit(Request $request, ApcCompetence $apcCompetence): Response
    {
        $form = $this->createForm(ApcCompetenceType::class, $apcCompetence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'apc.competence.edit.success.flash');

            return $this->redirectToRoute('administration_apc_competence_index',
                ['departement' => $apcCompetence->getDepartement()->getId()]);
        }

        return $this->render('competences/apc_competence/edit.html.twig', [
            'apc_competence' => $apcCompetence,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name:"administration_apc_competence_delete", methods:["DELETE"])]
    public function delete(Request $request, ApcCompetence $apcCompetence): Response
    {
        $departement = $apcCompetence->getDepartement();

        if ($this->isCsrfTokenValid('delete' . $apcCompetence->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($apcCompetence);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'apc.competence.delete.success.flash');
        }

        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'apc.competence.delete.error.flash');

        return $this->redirectToRoute('administration_apc_referentiel_index',
            [
                'departement' => $departement->getId(),
            ]);
    }

    #[Route("/{id}/duplicate", name:"administration_apc_competence_duplicate", methods:["GET","POST"])]
    public function duplicate(ApcCompetence $apcCompetence): Response
    {
        $newApcCompetence = clone $apcCompetence;

        $this->entityManager->persist($newApcCompetence);
        $this->entityManager->flush();
        $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'apc.competence.duplicate.success.flash');

        return $this->redirectToRoute('administration_apc_competence_edit', ['id' => $newApcCompetence->getId()]);
    }
}
