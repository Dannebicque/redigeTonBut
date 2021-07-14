<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcApprentissageCritiqueController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller\competences;

use App\Classes\Apc\ApcApprentissageCritiqueOrdre;
use App\Controller\BaseController;
use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcNiveau;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Form\ApcApprentissageCritiqueType;
use App\Repository\ApcApprentissageCritiqueRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/apc/apprentissage/critique")
 */
class ApcApprentissageCritiqueController extends BaseController
{
    #[Route('/{departement}', name: 'administration_apc_apprentissage_critique_index', requirements: ['departement' => '\d+'], methods: ['GET'])]
    public function index(
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        Departement $departement
    ): Response {
        $acs = $apcApprentissageCritiqueRepository->findByDepartement($departement);

        return $this->render('competences/apc_apprentissage_critique/index.html.twig', [
            'acs' => $acs,

        ]);
    }


    #[Route("/new/{niveau}", name:"administration_apc_apprentissage_critique_new", methods:["GET","POST"])]
    public function new(Request $request, ApcNiveau $niveau): Response
    {
        $apcApprentissageCritique = new ApcApprentissageCritique($niveau);
        $form = $this->createForm(ApcApprentissageCritiqueType::class, $apcApprentissageCritique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($apcApprentissageCritique);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Apprentissage critique ajouté avec succès.');

            return $this->redirectToRoute('administration_apc_competence_show',
                ['id' => $niveau->getCompetence()->getId()]);
        }

        return $this->render('competences/apc_apprentissage_critique/new.html.twig', [
            'apc_apprentissage_critique' => $apcApprentissageCritique,
            'form'                       => $form->createView(),
            'competence'                 => $niveau->getCompetence(),
        ]);
    }

    #[Route("/{id}/edit", name:"administration_apc_apprentissage_critique_edit", methods:["GET","POST"])]
    public function edit(Request $request,
        ApcApprentissageCritiqueOrdre $apcApprentissageCritiqueOrdre,
        ApcApprentissageCritique $apcApprentissageCritique): Response
    {
        //todo: a finir
        $form = $this->createForm(ApcApprentissageCritiqueType::class, $apcApprentissageCritique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcApprentissageCritiqueOrdre->deplaceApprentissageCritique($apcApprentissageCritique, $apcApprentissageCritique->getOrdre());
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Apprentissage critique modifié avec succès.');

            if (null !== $request->request->get('btn_update')) {
                return $this->redirectToRoute('administration_apc_referentiel_index',
                    ['departement' => $apcApprentissageCritique->getDepartement()->getId()]);
            }
        }

        return $this->render('competences/apc_apprentissage_critique/edit.html.twig', [
            'apc_apprentissage_critique' => $apcApprentissageCritique,
            'form'                       => $form->createView(),
        ]);
    }

    #[Route('/{id}/up', name: 'administration_apc_apprentissage_critique_up', methods: ['GET'])]
    public function up(
        Request $request,
        ApcApprentissageCritique $apcApprentissageCritique
    ): Response {

    }

    #[Route('/{id}/down', name: 'administration_apc_apprentissage_critique_down', methods: ['GET'])]
    public function down(
        Request $request,
        ApcApprentissageCritique $apcApprentissageCritique
    ): Response {

    }

    #[Route("/{id}", name:"administration_apc_apprentissage_critique_delete", methods:["DELETE"])]
    public function delete(Request $request, ApcApprentissageCritique $apcApprentissageCritique): Response
    {//todo: a finir
        if ($this->isCsrfTokenValid('delete' . $apcApprentissageCritique->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($apcApprentissageCritique);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Apprentissage critique supprimé avec succès.');
        }
        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'Erreur lors de la suppression de l\'apprentissage critique.');

        return $this->redirectToRoute('administration_apc_apprentissage_critique_index');
    }
}
