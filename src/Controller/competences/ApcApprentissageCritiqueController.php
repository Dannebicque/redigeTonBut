<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcApprentissageCritiqueController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller\competences;

use App\Classes\Apc\ApcApprentissageCritiqueExport;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route("/apc/apprentissage/critique")]
class ApcApprentissageCritiqueController extends BaseController
{
    #[Route('/export', name: 'administration_apc_apprentissage_critique_export', methods: ['GET'])]
    public function export(
        ApcApprentissageCritiqueExport $apcApprentissageCritiqueExport,
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
    ): Response {
        $acs = $apcApprentissageCritiqueRepository->findByDepartement($this->getDepartement());

        return $apcApprentissageCritiqueExport->exportDepartement($acs, $this->getDepartement());
    }


    #[Route('/{departement}', name: 'administration_apc_apprentissage_critique_index', requirements: ['departement' => '\d+'], methods: ['GET'])]
    public function index(
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        Departement $departement
    ): Response {
        if ($this->getDepartement()?->getId() !== $departement->getId()) {
            throw new AccessDeniedException();
        }
        $acs = $apcApprentissageCritiqueRepository->findByDepartement($departement);

        return $this->render('competences/apc_apprentissage_critique/index.html.twig', [
            'acs' => $acs,

        ]);
    }

    #[Route("/new/{niveau}", name: "administration_apc_apprentissage_critique_new", methods: ["GET", "POST"])]
    public function new(Request $request, ApcApprentissageCritiqueOrdre $apcApprentissageCritiqueOrdre, ApcNiveau $niveau): Response
    {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $niveau->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        $apcApprentissageCritique = new ApcApprentissageCritique($niveau);
        $form = $this->createForm(ApcApprentissageCritiqueType::class, $apcApprentissageCritique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcApprentissageCritique->setCode('Err');
            $this->entityManager->persist($apcApprentissageCritique);
            $this->entityManager->flush();
            $apcApprentissageCritiqueOrdre->deplaceApprentissageCritique($apcApprentissageCritique, $apcApprentissageCritique->getOrdre());
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Apprentissage critique ajouté avec succès.');

            return $this->redirectToRoute('administration_apc_referentiel_index',
                ['departement' => $niveau->getDepartement()?->getId()]);
        }

        return $this->render('competences/apc_apprentissage_critique/new.html.twig', [
            'apc_apprentissage_critique' => $apcApprentissageCritique,
            'form' => $form->createView(),
            'competence' => $niveau->getCompetence(),
            'niveau' => $niveau
        ]);
    }

    #[Route("/{id}/edit", name: "administration_apc_apprentissage_critique_edit", methods: ["GET", "POST"])]
    public function edit(
        Request $request,
        ApcApprentissageCritiqueOrdre $apcApprentissageCritiqueOrdre,
        ApcApprentissageCritique $apcApprentissageCritique
    ): Response {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $apcApprentissageCritique->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        $ordre = $apcApprentissageCritique->getOrdre();
        $form = $this->createForm(ApcApprentissageCritiqueType::class, $apcApprentissageCritique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcApprentissageCritiqueOrdre->deplaceApprentissageCritique($apcApprentissageCritique, $ordre);
            $this->entityManager->flush();//todo: vérifier doublon ou inversion? Mais dans ce cas, il faut garder la valeur d'origine
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Apprentissage critique modifié avec succès.');

            if (null !== $request->request->get('btn_update')) {
                return $this->redirectToRoute('administration_apc_referentiel_index',
                    ['departement' => $apcApprentissageCritique->getDepartement()->getId()]);
            }

            return $this->redirectToRoute('administration_apc_apprentissage_critique_edit',
                ['id' => $apcApprentissageCritique->getId()]);
        }

        return $this->render('competences/apc_apprentissage_critique/edit.html.twig', [
            'apc_apprentissage_critique' => $apcApprentissageCritique,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/deplace/{position}', name: 'administration_apc_apprentissage_critique_deplace', methods: ['GET'])]
    public function deplace(
        Request $request,
        ApcApprentissageCritiqueOrdre $apcRessourceOrdre,
        ApcApprentissageCritique $apcApprentissageCritique,
        int $position
    ): Response {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $apcApprentissageCritique->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        //todo: a confirmer $this->denyAccessUnlessGranted('edit', $apcApprentissageCritique);
        $apcRessourceOrdre->deplaceApprentissageCritiquePosition($apcApprentissageCritique, $position);

        return $this->redirect($request->headers->get('referer'));

    }

    #[Route('/{id}/delete', name: 'administration_apc_apprentissage_critique_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ApcApprentissageCritique $apcApprentissageCritique
    ): Response {
        if ($this->getDepartement()?->getVerouilleCompetences() === true || $this->getDepartement()?->getId() !== $apcApprentissageCritique->getDepartement()?->getId()) {
            throw new AccessDeniedException();
        }

        $this->denyAccessUnlessGranted('delete', $apcApprentissageCritique);
        $departement = $apcApprentissageCritique->getDepartement()->getId();
        $id = $apcApprentissageCritique->getId();
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            foreach ($apcApprentissageCritique->getApcSaeApprentissageCritiques() as $s) {
                $this->entityManager->remove($s);
            }

            foreach ($apcApprentissageCritique->getApcRessourceApprentissageCritiques() as $s) {
                $this->entityManager->remove($s);
            }

            $this->entityManager->remove($apcApprentissageCritique);
            $this->entityManager->flush();
            $this->addFlashBag(
                Constantes::FLASHBAG_SUCCESS,
                'Apprentissage critique supprimé avec succès.'
            );

            return $this->redirectToRoute('administration_apc_referentiel_index',
                ['departement' => $departement]);
        }

        $this->addFlashBag(Constantes::FLASHBAG_ERROR, 'Erreur lors de la suppression de l\'apprentissage critique.');

        return $this->redirect($request->headers->get('referer'));
    }
}
