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
use Symfony\Component\Routing\Annotation\Route;

#[Route("/apc/parcours", name: "administration_")]
class ApcParcoursController extends BaseController
{
    #[Route("/{id}/edit", name: "apc_parcours_edit", methods: ["GET", "POST"])]
    public function edit(
        Request $request,
        ApcParcours $apcParcour
    ): Response {
        $form = $this->createForm(ApcParcoursType::class, $apcParcour);
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
}
