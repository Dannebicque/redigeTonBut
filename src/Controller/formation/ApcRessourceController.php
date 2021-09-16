<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcRessourceController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 19/05/2021 16:40
 */

namespace App\Controller\formation;


use App\Classes\Apc\ApcRessourceAddEdit;
use App\Classes\Apc\ApcRessourceOrdre;
use App\Controller\BaseController;
use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Form\ApcRessourceType;
use App\Repository\ApcComptenceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/formation/ressource", name="formation_")
 */
class ApcRessourceController extends BaseController
{
    #[Route("/new/{semestre}/{parcours}", name: "apc_ressource_new", options: ['expose' => true], methods: [
        "GET",
        "POST"
    ])]
    public function new(
        ApcComptenceRepository $apcComptenceRepository,
        ApcRessourceOrdre $apcRessourceOrdre,
        ApcRessourceAddEdit $apcRessourceAddEdit,
        Request $request,
        Semestre $semestre = null,
        ApcParcours $parcours = null
    ): Response {
        $this->denyAccessUnlessGranted('new', $semestre ?? $this->getDepartement());
        $apcRessource = new ApcRessource();

        if ($semestre !== null) {
            $apcRessource->setSemestre($semestre);
            $apcRessource->setOrdre($apcRessourceOrdre->getOrdreSuivant($semestre));
        }

        $form = $this->createForm(ApcRessourceType::class, $apcRessource, [
            'departement' => $this->getDepartement(),
            'editable' => $this->isGranted('ROLE_GT'),
            'parcours' => $parcours
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcRessourceAddEdit->addOrEdit($apcRessource, $request);
            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Ressource ajoutée avec succès.'
            );

            if ($parcours !== null) {
                return $this->redirectToRoute('but_ressources_annee',
                    [
                        'annee' => $apcRessource->getSemestre()->getAnnee()->getId(),
                        'semestre' => $apcRessource->getSemestre()->getId(),
                        'parcours' => $parcours->getId()
                    ]);
            }

            return $this->redirectToRoute('but_ressources_annee',
                [
                    'annee' => $apcRessource->getSemestre()->getAnnee()->getId(),
                    'semestre' => $apcRessource->getSemestre()->getId()
                ]);
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
        ApcRessourceAddEdit $apcRessourceAddEdit,
        Request $request,
        ApcRessource $apcRessource
    ): Response {
        $this->denyAccessUnlessGranted('edit', $apcRessource);
        $form = $this->createForm(ApcRessourceType::class, $apcRessource, [
            'departement' => $this->getDepartement(),
            'editable' => $this->isGranted('ROLE_GT')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $apcRessourceAddEdit->removeLiens($apcRessource);
            $apcRessourceAddEdit->addOrEdit($apcRessource, $request);

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Ressource modifiée avec succès.'
            );

            if (null !== $request->request->get('btn_update') && null !== $apcRessource->getSemestre() && null !== $apcRessource->getSemestre()->getAnnee()) {

                return $this->redirectToRoute('but_ressources_annee',
                    [
                        'annee' => $apcRessource->getSemestre()->getAnnee()->getId(),
                        'semestre' => $apcRessource->getSemestre()->getId(),
                        'parcours' => $request->query->get('parcours')
                    ]);
            }

            return $this->redirectToRoute('formation_apc_ressource_edit',
                ['id' => $apcRessource->getId(),'parcours' => $request->query->get('parcours')]);
        }

        return $this->render('formation/apc_ressource/edit.html.twig', [
            'apc_ressource' => $apcRessource,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/effacer", name="apc_ressource_delete", methods="post")
     */
    public function delete(Request $request, ApcRessource $apcRessource): Response
    {
        $this->denyAccessUnlessGranted('delete', $apcRessource);
        $id = $apcRessource->getId();
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $semestre = $apcRessource->getSemestre();
            $this->entityManager->remove($apcRessource);
            $this->entityManager->flush();
            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Ressource supprimée avec succès.'
            );

            return $this->redirectToRoute('but_ressources_annee', [
                'annee' => $semestre->getAnnee()->getId(),
                'semestre' => $semestre->getId(),
                'parcours' => $request->query->get('parcours')
            ]);
        }

        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'Erreur lors de la suppression de la ressource.');

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/{id}/duplicate", name="apc_ressource_duplicate", methods="GET|POST")
     */
    public function duplicate(
        ApcRessourceAddEdit $apcRessourceAddEdit,
        ApcRessource $apcRessource
    ): Response {
        $this->denyAccessUnlessGranted('duplicate', $apcRessource);
        $newApcRessource = $apcRessourceAddEdit->duplique($apcRessource);
        $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Ressource dupliquée avec succès.');

        return $this->redirectToRoute('formation_apc_ressource_edit', ['id' => $newApcRessource->getId()]);
    }

    /**
     * @Route("/{id}/deplace/{position}", name="apc_ressource_deplace", methods="GET")
     */
    public function deplace(
        Request $request,
        ApcRessourceOrdre $apcRessourceOrdre,
        ApcRessource $apcRessource,
        int $position
    ): Response {
        $this->denyAccessUnlessGranted('edit', $apcRessource);
        $apcRessourceOrdre->deplaceRessource($apcRessource, $position);

        return $this->redirect($request->headers->get('referer'));

    }
}
