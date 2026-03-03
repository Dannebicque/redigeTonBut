<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcCompetenceController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 01/06/2021 19:12
 */

namespace App\Controller\competences;

use App\Classes\Apc\ApcApprentissageCritiqueOrdre;
use App\Classes\Apc\ApcCompetenceOrdre;
use App\Controller\BaseController;
use App\Entity\ApcCompetence;
use App\Entity\ApcCompetenceSemestre;
use App\Entity\ApcNiveau;
use App\Entity\ApcParcours;
use App\Entity\Constantes;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Form\ApcCompetenceType;
use App\Repository\ApcCompetenceSemestreRepository;
use App\Repository\ApcParcoursRepository;
use App\Utils\Convert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/apc/competence")]
class ApcCompetenceController extends BaseController
{
    //création de la compétence
    #[Route("/new", name:"administration_apc_competence_new", methods:["GET","POST"])]
    public function new(
        ApcApprentissageCritiqueOrdre $apcApprentissageCritiqueOrdre,
        Request $request): Response
    {
        $apcCompetence = new ApcCompetence($this->getVersion());
        $form = $this->createForm(ApcCompetenceType::class, $apcCompetence, [
            'new' => true,
            'version' => $this->getVersion(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // par défaut, on met au dernier ordre disponible
            $lastCompetence = $this->entityManager->getRepository(ApcCompetence::class)->findLastByVersion($apcCompetence->getVersion());
            $apcCompetence->setNumero($lastCompetence->getNumero() + 1);
            $apcCompetence->setNumeroIdentifiant($lastCompetence->getNumero() + 1);
            $apcCompetence->setCouleur('c' . $apcCompetence->getNumero());
            $this->entityManager->persist($apcCompetence);

            // parcours les niveaux, puis les AC pour générer les codes des AC
            foreach ($apcCompetence->getApcNiveaux() as $apcNiveau) {
                foreach ($apcNiveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {
                    $apcApprentissageCritiqueOrdre->deplaceApprentissageCritique($apcApprentissageCritique, $apcApprentissageCritique->getOrdre());
                }
            }


            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Compétence créée avec succès.');

            return $this->redirectToRoute('administration_apc_referentiel_index',
                ['version' => $apcCompetence->getVersion()?->getId()]);
        }

        return $this->render('competences/apc_competence/new.html.twig', [
            'apc_competence' => $apcCompetence,
            'form' => $form->createView(),
            'version' => $this->getVersion()
        ]);
    }


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
        $form = $this->createForm(ApcCompetenceType::class, $apcCompetence, [
            'new' => false,
            'version' => $this->getVersion(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apcCompetenceOrdre->deplaceCompetence($apcCompetence, $ordre);
            $this->entityManager->flush();
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Compétence modifiée avec succès.');

            if (null !== $request->request->get('btn_update')) {
                return $this->redirectToRoute('administration_apc_referentiel_index',
                    ['version' => $apcCompetence->getVersion()->getId()]);

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
        Request $request, Semestre $semestre, ApcCompetence $competence): \Symfony\Component\HttpFoundation\JsonResponse {
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
            if ($semestre->getVersion()?->getDepartement()->getTypeStructure() !== Departement::TYPE3 && $parcours !== null) {
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
            if ($semestre->getVersion()?->getDepartement()->getTypeStructure() !== Departement::TYPE3 && $parcours !== null) {
                foreach ($semestre->getVersion()->getApcParcours() as $parc) {
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

    #[Route('/{id}/delete', name: 'administration_apc_competence_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ApcCompetence $apcCompetence
    ): Response {

        $this->denyAccessUnlessGranted('COMPETENCES_DELETE', $apcCompetence);

        if ($this->isCsrfTokenValid('delete'.$apcCompetence->getId(), $request->request->get('_token'))) {
            $version = $apcCompetence->getVersion();
            if ($version === null) {
                throw $this->createNotFoundException('Departement introuvable');
            }

            if ($this->getDataUserSession()?->getVersion()?->isVerouilleCompetences() === true) {
                $this->addFlashBag('warning', 'Le référentiel est verrouillé, vous ne pouvez pas supprimer une compétence');
                return $this->redirectToRoute('administration_apc_referentiel_index', ['version' => $version->getId()]);
            }

            foreach ($apcCompetence->getApcNiveaux() as $apcNiveau) {
                foreach ($apcNiveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {
                    foreach ($apcApprentissageCritique->getApcRessourceApprentissageCritiques() as $apcRessourceApprentissageCritique) {
                        $this->entityManager->remove($apcRessourceApprentissageCritique);
                    }
                    foreach ($apcApprentissageCritique->getApcSaeApprentissageCritiques() as $apcSaeApprentissageCritique) {
                        $this->entityManager->remove($apcSaeApprentissageCritique);
                    }
                    $this->entityManager->remove($apcApprentissageCritique);
                }


                foreach ($apcNiveau->getApcParcoursNiveaux() as $apcParcoursNiveau) {
                    $this->entityManager->remove($apcParcoursNiveau);
                }
                $this->entityManager->remove($apcNiveau);
            }

            foreach ($apcCompetence->getApcComposanteEssentielles() as $apcComposanteEssentielle) {
                $this->entityManager->remove($apcComposanteEssentielle);
            }

            foreach ($apcCompetence->getApcSituationProfessionnelles() as $apcSituationProfessionnelle) {
                $this->entityManager->remove($apcSituationProfessionnelle);
            }

            foreach ($apcCompetence->getApcRessourceCompetences() as $apcRessourceCompetence) {
                $apcRessourceCompetence->getCompetence()?->removeApcRessourceCompetence($apcRessourceCompetence);

                $this->entityManager->remove($apcRessourceCompetence);
            }

            foreach ($apcCompetence->getApcSaeCompetences() as $apcSaeCompetence) {
                $apcSaeCompetence->getCompetence()?->removeApcSaeCompetence($apcSaeCompetence);
                $this->entityManager->remove($apcSaeCompetence);
            }

            foreach ($apcCompetence->getApcCompetenceSemestres() as $apcCompetenceSemestre) {
                $this->entityManager->remove($apcCompetenceSemestre);
            }

            $this->entityManager->remove($apcCompetence);
            $this->entityManager->flush();

            $this->addFlashBag('success', 'Compétence supprimée, avec l\'ensemble des liens vers les ressources, SAE et parcours');

            return $this->redirectToRoute('administration_apc_referentiel_index', ['version' => $version->getId()]);
        }
    }
}
