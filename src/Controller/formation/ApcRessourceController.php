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
use App\Entity\Semestre;
use App\Event\RessourceEvent;
use App\Form\ApcRessourceType;
use App\Repository\ApcParcoursRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
        EventDispatcherInterface $eventDispatcher,
        ApcRessourceOrdre $apcRessourceOrdre,
        ApcRessourceAddEdit $apcRessourceAddEdit,
        Request $request,
        Semestre $semestre = null,
        ApcParcours $parcours = null
    ): Response {
        if ($this->getDepartement()->getVerouilleCroise() === false) {
            $this->denyAccessUnlessGranted('new', $semestre ?? $this->getDepartement());
            $apcRessource = new ApcRessource();

            if ($semestre !== null) {
                $apcRessource->setSemestre($semestre);
                $apcRessource->setOrdre($apcRessourceOrdre->getOrdreSuivant($semestre));
            }

            $form = $this->createForm(ApcRessourceType::class, $apcRessource, [
                'departement' => $this->getDepartement(),
                'editable' => $this->isGranted('ROLE_GT'),
                'verouille_croise' => $this->getDepartement()?->getVerouilleCroise(),
                'parcours' => $parcours
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $apcRessourceAddEdit->addOrEdit($apcRessource, $request, $this->getDepartement()->getVerouilleCroise());

                $ressourceEvent = new RessourceEvent($apcRessource);
                $eventDispatcher->dispatch($ressourceEvent, RessourceEvent::UPDATE_CODIFICATION);

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
        return $this->redirectToRoute('homepage');

    }

    /**
     * @Route("/{id}/edit", name="apc_ressource_edit", methods={"GET","POST"})
     */
    public function edit(
        EventDispatcherInterface $eventDispatcher,
        ApcParcoursRepository $apcParcoursRepository,
        ApcRessourceAddEdit $apcRessourceAddEdit,
        Request $request,
        ApcRessource $apcRessource
    ): Response {
        if ($this->getDepartement()->getPnBloque() === false) {

            $this->denyAccessUnlessGranted('edit', $apcRessource);

            $parc = $request->query->get('parcours');
            $parcours = null;
            if ($parc !== null) {
                $parcours = $apcParcoursRepository->find($parc);
            }

            $form = $this->createForm(ApcRessourceType::class, $apcRessource, [
                'departement' => $this->getDepartement(),
                'editable' => $this->isGranted('ROLE_GT'),
                'verouille_croise' => $this->getDepartement()?->getVerouilleCroise(),
                'parcours' => $parcours,
                'ordre' => $apcRessource->getSemestre()->getOrdreLmd()
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $apcRessourceAddEdit->removeLiens($apcRessource, $this->getDepartement()->getVerouilleCroise());
                $apcRessourceAddEdit->addOrEdit($apcRessource, $request, $this->getDepartement()->getVerouilleCroise());

                $ressourceEvent = new RessourceEvent($apcRessource);
                $eventDispatcher->dispatch($ressourceEvent, RessourceEvent::UPDATE_CODIFICATION);

                $this->addFlashBag(
                    Constantes::FLASHBAG_SUCCESS,
                    'Ressource modifiée avec succès.'
                );

                if (null !== $request->request->get('btn_update') && null !== $apcRessource->getSemestre() && null !== $apcRessource->getSemestre()->getAnnee()) {

                    if ($parcours === null) {
                        return $this->redirectToRoute('but_ressources_annee',
                            [
                                'annee' => $apcRessource->getSemestre()->getAnnee()->getId(),
                                'semestre' => $apcRessource->getSemestre()->getId()
                            ]);
                    }

                    return $this->redirectToRoute('but_ressources_annee',
                        [
                            'annee' => $apcRessource->getSemestre()->getAnnee()->getId(),
                            'semestre' => $apcRessource->getSemestre()->getId(),
                            'parcours' => $parcours->getId()
                        ]);
                }

                if ($parcours === null) {
                    return $this->redirectToRoute('formation_apc_ressource_edit',
                        ['id' => $apcRessource->getId()]);
                }

                return $this->redirectToRoute('formation_apc_ressource_edit',
                    ['id' => $apcRessource->getId(), 'parcours' => $parcours->getId()]);
            }

            if ($parcours === null) {
                return $this->render('formation/apc_ressource/edit.html.twig', [
                    'apc_ressource' => $apcRessource,
                    'form' => $form->createView()
                ]);
            }

            return $this->render('formation/apc_ressource/edit.html.twig', [
                'apc_ressource' => $apcRessource,
                'form' => $form->createView(),
                'parcours' => $parcours->getId()
            ]);
        }
        return $this->redirectToRoute('homepage');

    }

    /**
     * @Route("/{id}/effacer", name="apc_ressource_delete", methods="post")
     */
    public function delete(Request $request, ApcRessource $apcRessource): Response
    {
        if ($this->getDepartement()->getPnBloque() === false) {

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
        return $this->redirectToRoute('homepage');

    }

    /**
     * @Route("/{id}/duplicate", name="apc_ressource_duplicate", methods="GET|POST")
     */
    public function duplicate(
        ApcRessourceAddEdit $apcRessourceAddEdit,
        ApcRessource $apcRessource
    ): Response {
        if ($this->getDepartement()->getPnBloque() === false) {

            $this->denyAccessUnlessGranted('duplicate', $apcRessource);
            $newApcRessource = $apcRessourceAddEdit->duplique($apcRessource);
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Ressource dupliquée avec succès.');

            return $this->redirectToRoute('formation_apc_ressource_edit', ['id' => $newApcRessource->getId()]);
        }
        return $this->redirectToRoute('homepage');
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
        if ($this->getDepartement()->getPnBloque() === false) {
            $this->denyAccessUnlessGranted('edit', $apcRessource);
            $apcRessourceOrdre->deplaceRessource($apcRessource, $position);
            return $this->redirect($request->headers->get('referer'));
        }
        return $this->redirectToRoute('homepage');
    }
}
