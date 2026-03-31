<?php

namespace App\Controller\competences;

use App\Classes\Apc\ApcComposanteEssentielleOrdre;
use App\Controller\BaseController;
use App\Entity\ApcCompetence;
use App\Entity\ApcComposanteEssentielle;
use App\Entity\ApcNiveau;
use App\Entity\Constantes;
use App\Form\ApcComposanteEssentielleType;
use App\Form\ApcNiveauType;
use App\Utils\Codification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/apc/niveau', name:'administration_')]
class ApcNiveauController extends BaseController
{
    #[Route('/new/{competence}', name: 'apc_niveau_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
        ApcComposanteEssentielleOrdre $apcComposanteEssentielleOrdre,
        ApcCompetence $competence): Response
    {
        if ($this->getVersion()?->isVerouilleCompetences() === true || $this->getVersion()?->getId() !== $competence->getVersion()?->getId()) {
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
                ['departement' => $apcComposanteEssentielle->getVersion()?->getId()]);
        }

        return $this->render('competences/apc_niveau/new.html.twig', [
            'apc_niveau' => $apcComposanteEssentielle,
            'form' => $form->createView(),
            'competence' => $competence
        ]);
    }

    #[Route('/{id}/edit', name: 'apc_niveau_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,
                         Codification $codification,
        ApcNiveau $apcNiveau): Response
    {
        if ($this->getVersion()?->isVerouilleCompetences() === true || $this->getVersion()?->getId() !== $apcNiveau->getCompetence()?->getVersion()?->getId()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ApcNiveauType::class, $apcNiveau, [
            'departement' => $apcNiveau->getCompetence()?->getVersion(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $acs = $apcNiveau->getApcApprentissageCritiques();
            foreach ($acs as $ac) {
                $ac->setCode($codification::codeApprentissageCritique($ac));
            }


            $this->entityManager->flush();

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Niveau modifiée avec succès.'
            );

            if (null !== $request->request->get('btn_update')) {
                return $this->redirectToRoute('administration_apc_referentiel_index',
                    ['version' => $apcNiveau->getCompetence()?->getVersion()?->getId()]);
            }

            return $this->redirectToRoute('administration_apc_niveau_edit',
                ['id' => $apcNiveau->getId()]);
        }

        return $this->render('competences/apc_niveau/edit.html.twig', [
            'apc_niveau' => $apcNiveau,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'apc_niveau_delete', methods: ['POST'])]
    public function delete(Request $request, ApcNiveau $apcNiveau): Response
    {
        if ($this->getVersion()?->isVerouilleCompetences() === true || $this->getVersion()?->getId() !== $apcNiveau->getCompetence()?->getVersion()?->getId()) {
            throw new AccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $apcNiveau->getId(), $request->request->get('_token'))) {

            foreach ($apcNiveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {

                foreach ($apcApprentissageCritique->getApcSaeApprentissageCritiques() as $s) {
                    $this->entityManager->remove($s);
                }

                foreach ($apcApprentissageCritique->getApcRessourceApprentissageCritiques() as $s) {
                    $this->entityManager->remove($s);
                }

                $this->entityManager->remove($apcApprentissageCritique);
            }

            foreach ($apcNiveau->getApcParcoursNiveaux() as $apcParcoursNiveau) {
                $this->entityManager->remove($apcParcoursNiveau);
            }

            $this->entityManager->remove($apcNiveau);
            $this->entityManager->flush();

            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Niveau supprimée avec succès.'
            );
        }

        return $this->redirectToRoute('administration_apc_referentiel_index',
            ['version' => $apcNiveau->getCompetence()?->getVersion()?->getId()]);
    }
}
