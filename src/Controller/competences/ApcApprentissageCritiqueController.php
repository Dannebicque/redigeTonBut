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
use App\Classes\Apc\ApcRessourceOrdre;
use App\Controller\BaseController;
use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcNiveau;
use App\Entity\ApcRessource;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Form\ApcApprentissageCritiqueType;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Utils\Codification;
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
        ApcApprentissageCritique $apcApprentissageCritique): Response
    {
        $form = $this->createForm(ApcApprentissageCritiqueType::class, $apcApprentissageCritique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcApprentissageCritique->setCode(Codification::codeApprentissageCritique($apcApprentissageCritique));
            $this->entityManager->flush();//todo: vérifier doublon ou inversion? Mais dans ce cas, il faut garder la valeur d'origine
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

    #[Route('/{id}/deplace/{position}', name: 'administration_apc_apprentissage_critique_deplace', methods: ['GET'])]
    public function deplace(
        Request $request,
        ApcApprentissageCritiqueOrdre $apcRessourceOrdre,
        ApcApprentissageCritique $apcApprentissageCritique, int $position): Response
    {
        //todo: a confirmer $this->denyAccessUnlessGranted('edit', $apcApprentissageCritique);
        $apcRessourceOrdre->deplaceApprentissageCritique($apcApprentissageCritique, $position);

        return $this->redirect($request->headers->get('referer'));

    }
}
