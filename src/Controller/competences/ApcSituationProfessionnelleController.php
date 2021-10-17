<?php

namespace App\Controller\competences;

use App\Controller\BaseController;
use App\Entity\ApcCompetence;
use App\Entity\ApcSituationProfessionnelle;
use App\Entity\Constantes;
use App\Form\ApcSituationProfessionnelleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/apc/situation/professionnelle', name:'administration_')]
class ApcSituationProfessionnelleController extends BaseController
{
    #[Route('/new/{competence}', name: 'apc_situation_professionnelle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ApcCompetence $competence): Response
    {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $competence->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        $apcSituationProfessionnelle = new ApcSituationProfessionnelle();
        $apcSituationProfessionnelle->setCompetence($competence);
        $form = $this->createForm(ApcSituationProfessionnelleType::class, $apcSituationProfessionnelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($apcSituationProfessionnelle);
            $this->entityManager->flush();

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Situation professionnelle ajoutée avec succès.'
            );

            return $this->redirectToRoute('administration_apc_referentiel_index',
                ['departement' => $apcSituationProfessionnelle->getDepartement()?->getId()]);
        }

        return $this->renderForm('competences/apc_situation_professionnelle/new.html.twig', [
            'apc_situation_professionnelle' => $apcSituationProfessionnelle,
            'form' => $form,
            'competence' => $competence
        ]);
    }

    #[Route('/{id}/edit', name: 'apc_situation_professionnelle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ApcSituationProfessionnelle $apcSituationProfessionnelle): Response
    {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $apcSituationProfessionnelle->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ApcSituationProfessionnelleType::class, $apcSituationProfessionnelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Situation professionnelle modifiée avec succès.'
            );

            if (null !== $request->request->get('btn_update')) {
                return $this->redirectToRoute('administration_apc_referentiel_index',
                    ['departement' => $apcSituationProfessionnelle?->getDepartement()?->getId()]);
            }

            return $this->redirectToRoute('administration_apc_situation_professionnelle_edit',
                ['id' => $apcSituationProfessionnelle->getId()]);

        }

        return $this->renderForm('competences/apc_situation_professionnelle/edit.html.twig', [
            'apc_situation_professionnelle' => $apcSituationProfessionnelle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'apc_situation_professionnelle_delete', methods: ['POST'])]
    public function delete(Request $request, ApcSituationProfessionnelle $apcSituationProfessionnelle): Response
    {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $apcSituationProfessionnelle->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$apcSituationProfessionnelle->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($apcSituationProfessionnelle);
            $this->entityManager->flush();

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Situation professionnelle supprimée avec succès.'
            );

            return $this->redirectToRoute('administration_apc_referentiel_index',
                ['departement' => $apcSituationProfessionnelle?->getDepartement()?->getId()]);
        }

        $this->addFlashBag(
            Constantes::FLASHBAG_ERROR,
            'Erreur lors de la suppression de la situation professionnelle.'
        );
    }
}
