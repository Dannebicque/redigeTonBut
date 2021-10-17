<?php

namespace App\Controller\competences;

use App\Classes\Apc\ApcComposanteEssentielleOrdre;
use App\Controller\BaseController;
use App\Entity\ApcCompetence;
use App\Entity\ApcComposanteEssentielle;
use App\Entity\Constantes;
use App\Form\ApcComposanteEssentielleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/apc/composante/essentielle', name:'administration_')]
class ApcComposanteEssentielleController extends BaseController
{
    #[Route('/new/{competence}', name: 'apc_composante_essentielle_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
        ApcComposanteEssentielleOrdre $apcComposanteEssentielleOrdre,
        ApcCompetence $competence): Response
    {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $competence->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        $apcComposanteEssentielle = new ApcComposanteEssentielle();
        $apcComposanteEssentielle->setCompetence($competence);
        $form = $this->createForm(ApcComposanteEssentielleType::class, $apcComposanteEssentielle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($apcComposanteEssentielle);
            $this->entityManager->flush();
            $apcComposanteEssentielleOrdre->deplaceApcComposanteEssentielle($apcComposanteEssentielle, $apcComposanteEssentielle->getOrdre());
            $this->entityManager->flush();

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Composante essentielle ajoutée avec succès.'
            );
            return $this->redirectToRoute('administration_apc_referentiel_index',
                ['departement' => $apcComposanteEssentielle->getDepartement()?->getId()]);
        }

        return $this->renderForm('competences/apc_composante_essentielle/new.html.twig', [
            'apc_composante_essentielle' => $apcComposanteEssentielle,
            'form' => $form,
            'competence' => $competence
        ]);
    }

    #[Route('/{id}/edit', name: 'apc_composante_essentielle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,
        ApcComposanteEssentielleOrdre $apcComposanteEssentielleOrdre,
        ApcComposanteEssentielle $apcComposanteEssentielle): Response
    {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $apcComposanteEssentielle->getCompetence()?->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ApcComposanteEssentielleType::class, $apcComposanteEssentielle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcComposanteEssentielleOrdre->deplaceApcComposanteEssentielle($apcComposanteEssentielle, $apcComposanteEssentielle->getOrdre());

            $this->entityManager->flush();

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Composante essentielle modifiée avec succès.'
            );

            if (null !== $request->request->get('btn_update')) {
                return $this->redirectToRoute('administration_apc_referentiel_index',
                    ['departement' => $apcComposanteEssentielle?->getDepartement()?->getId()]);
            }

            return $this->redirectToRoute('administration_apc_composante_essentielle_edit',
                ['id' => $apcComposanteEssentielle->getId()]);
        }

        return $this->renderForm('competences/apc_composante_essentielle/edit.html.twig', [
            'apc_composante_essentielle' => $apcComposanteEssentielle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'apc_composante_essentielle_delete', methods: ['POST'])]
    public function delete(Request $request, ApcComposanteEssentielle $apcComposanteEssentielle): Response
    {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $apcComposanteEssentielle->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$apcComposanteEssentielle->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($apcComposanteEssentielle);
            $this->entityManager->flush();

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Composante essentielle supprimée avec succès.'
            );

            return $this->redirectToRoute('administration_apc_referentiel_index', [
                'departement' => $apcComposanteEssentielle?->getDepartement()?->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        $this->addFlashBag(
            Constantes::FLASHBAG_ERROR,
            'Erreur lors de la suppression de la composante essentielle.'
        );
    }
}
