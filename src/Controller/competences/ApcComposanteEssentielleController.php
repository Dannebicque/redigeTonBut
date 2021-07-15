<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcComposanteEssentielleController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller\competences;

use App\Controller\BaseController;
use App\Entity\ApcCompetence;
use App\Entity\ApcComposanteEssentielle;
use App\Entity\Constantes;
use App\Form\ApcComposanteEssentielleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/apc/composante/essentielle")]
class ApcComposanteEssentielleController extends BaseController
{
    #[Route("/{competence}/new", name:"administration_apc_composante_essentielle_new", methods:["GET","POST"])]
    public function new(Request $request, ApcCompetence $competence): Response
    {
        $apcComposanteEssentielle = new ApcComposanteEssentielle($competence);
        $form = $this->createForm(ApcComposanteEssentielleType::class, $apcComposanteEssentielle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($apcComposanteEssentielle);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Composante essentielle ajoutée avec succès.');

            return $this->redirectToRoute('administration_apc_competence_show', ['id' => $competence->getId()]);
        }

        return $this->render('competences/apc_composante_essentielle/new.html.twig', [
            'apc_composante_essentielle' => $apcComposanteEssentielle,
            'form'                       => $form->createView(),
            'competence'                 => $competence,
        ]);
    }

    #[Route("/{id}/edit", name:"administration_apc_composante_essentielle_edit", methods:["GET","POST"])]
    public function edit(Request $request, ApcComposanteEssentielle $apcComposanteEssentielle): Response
    {
        $form = $this->createForm(ApcComposanteEssentielleType::class, $apcComposanteEssentielle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Composante essentielle modifiée avec succès.');

            return $this->redirectToRoute('administration_apc_composante_essentielle_index');
        }

        return $this->render('competences/apc_composante_essentielle/edit.html.twig', [
            'apc_composante_essentielle' => $apcComposanteEssentielle,
            'form'                       => $form->createView(),
        ]);
    }

    #[Route("/{id}", name:"administration_apc_composante_essentielle_delete", methods:["DELETE"])]
    public function delete(Request $request, ApcComposanteEssentielle $apcComposanteEssentielle): Response
    {
        if ($this->isCsrfTokenValid('delete' . $apcComposanteEssentielle->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($apcComposanteEssentielle);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Composante essentielle supprimée avec succès.');
        }
        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'Erreur lors de la suppression de la composante essentielle.');

        return $this->redirectToRoute('administration_apc_composante_essentielle_index');
    }
}
