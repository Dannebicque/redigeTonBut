<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcCompetenceController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 01/06/2021 19:12
 */

namespace App\Controller\competences;

use App\Classes\Apc\ApcCompetenceOrdre;
use App\Controller\BaseController;
use App\Entity\ApcCompetence;
use App\Entity\ApcCompetenceSemestre;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Form\ApcCompetenceType;
use App\Repository\ApcCompetenceSemestreRepository;
use App\Repository\ApcParcoursRepository;
use App\Utils\Convert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/apc/competence")]
class ApcCompetenceController extends BaseController
{
     #[Route("/{id}/detail", name:"administration_apc_competence_show", methods:["GET"])]
    public function show(ApcCompetence $apcCompetence): Response
    {
        return $this->render('competences/apc_competence/show.html.twig', [
            'competence' => $apcCompetence,
        ]);
    }

     #[Route("/{id}/edit", name:"administration_apc_competence_edit", methods:["GET","POST"])]
    public function edit(
        ApcCompetenceOrdre $apcCompetenceOrdre,
        Request $request, ApcCompetence $apcCompetence): Response
    {
        $ordre = $apcCompetence->getCouleur();
        $form = $this->createForm(ApcCompetenceType::class, $apcCompetence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcCompetenceOrdre->deplaceCompetence($apcCompetence, $ordre);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Compétence modifiée avec succès.');

            if (null !== $request->request->get('btn_update')) {
                return $this->redirectToRoute('administration_apc_referentiel_index',
                    ['departement' => $apcCompetence->getDepartement()->getId()]);

            }
        }

        return $this->render('competences/apc_competence/edit.html.twig', [
            'apc_competence' => $apcCompetence,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{semestre}/{competence}/update_ects_ajax", name:"administration_apc_competence_update_ects", methods:["POST"], options:["expose"=>true])]
    public function updateEcts(
        ApcParcoursRepository $apcParcoursRepository,
        ApcCompetenceSemestreRepository $apcCompetenceSemestreRepository,
        Request $request, Semestre $semestre, ApcCompetence $competence) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        //regarde si déjà existant
        $apcCompSemetre = $apcCompetenceSemestreRepository->findOneBy(['semestre' => $semestre->getId(), 'competence' => $competence->getId()]);

        if (array_key_exists('parcours', $parametersAsArray)) {
            $parcours = $apcParcoursRepository->find($parametersAsArray['parcours']);
        } else {
            $parcours = null;
        }

        if ($apcCompSemetre !== null) {
            //on modifie
            if ($semestre->getDepartement()->getTypeStructure() !== Departement::TYPE3 && $parcours !== null) {
                $tab = $apcCompSemetre->getEctsParcours();
                $tab[$parcours->getId()] = Convert::convertToFloat($parametersAsArray['valeur']);
                $apcCompSemetre->setEctsParcours($tab);
            } else {
                $apcCompSemetre->setECTS(Convert::convertToFloat($parametersAsArray['valeur']));
            }
        } else {
            //on ajoute
            $apcCompSemetre = new ApcCompetenceSemestre();
            $apcCompSemetre->setSemestre($semestre);
            $apcCompSemetre->setCompetence($competence);
            if ($semestre->getDepartement()->getTypeStructure() !== Departement::TYPE3 && $parcours !== null) {
                foreach ($semestre->getDepartement()->getApcParcours() as $parc) {
                    $tab[$parc->getId()] = 0;
                }
                $apcCompSemetre->setECTS(0);
                $tab[$parcours->getId()] = Convert::convertToFloat($parametersAsArray['valeur']);
                $apcCompSemetre->setEctsParcours($tab);
            } else {
                $apcCompSemetre->setECTS(Convert::convertToFloat($parametersAsArray['valeur']));
            }
            $this->entityManager->persist($apcCompSemetre);
        }
        $this->entityManager->flush();

        return $this->json(true);
    }
}
