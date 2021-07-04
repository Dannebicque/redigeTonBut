<?php

namespace App\Controller;

use App\Classes\Tableau\Preconisation;
use App\Classes\Tableau\Structure;
use App\Entity\Annee;
use App\Entity\Semestre;
use App\Repository\ApcCompetenceSemestreRepository;
use App\Repository\ApcComptenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcRessourceCompetenceRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeCompetenceRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\SemestreRepository;
use App\Utils\Convert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tableau', name: 'tableau_')]
class TableauController extends BaseController
{
    #[Route('/structure', name: 'structure')]
    public function structure(): Response
    {
        return $this->render('tableau/structure.html.twig', [
        ]);
    }

    #[Route('/api-structure', name: 'api_structure', options: ["expose" => true])]
    public function apiStructure(
        Structure $structure,
        SemestreRepository $semestreRepository
    ): Response {
        $semestres = $semestreRepository->findByDepartement($this->getDepartement());
        $json = $structure->setSemestres($semestres)->getDataJson();

        return $this->json($json);
    }

    #[Route('/api-preconisation', name: 'api_preconisation', options: ['expose' => true])]
    public function apiPreconisation(
        Preconisation $preconisation,
        SemestreRepository $semestreRepository,
        ApcComptenceRepository $apcComptenceRepository
    ): Response {
        $semestres = $semestreRepository->findByDepartement($this->getDepartement());
        $competences = $apcComptenceRepository->findByDepartement($this->getDepartement());
        $json = $preconisation->setSemestresCompetences($semestres, $competences)->getDataJson();

        return $this->json($json);
    }

    #[Route('/api-structure-update', name: 'api_structure_update', options: ['expose' => true])]
    public function apiStructureUpdate(
        SemestreRepository $semestreRepository,
        Request $request
    ) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        $semestre = $semestreRepository->findSemestre($this->getDepartement(), $parametersAsArray['semestre']);
        if ($semestre !== null) {//todo: et vériifer lien semestre/département

            switch ($parametersAsArray['champ']) {
                case 'nbHeuresRessourcesSae':
                    $semestre->setNbHeuresRessourceSae(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'pourcentageAdaptationLocale':
                    $semestre->setPourcentageAdaptationLocale(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbHeuresSae':
                    $semestre->setNbHeuresSae(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbHeuresRessources':
                    $semestre->setNbHeuresRessources(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbSemainesStageMin':
                    $semestre->setNbSemaineStageMin(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbSemainesStageMax':
                    $semestre->setNbSemainesStageMax(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbHeuresProjet':
                    $semestre->setNbHeuresProjet(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
            }

            $this->entityManager->flush();

            return $this->json($parametersAsArray);
        }

        return $this->json(false);
    }

    #[Route('/croise/{annee}', name: 'croise_annee', requirements: ['annee' => '\d+'])]
    public function tableau(
        Annee $annee
    ): Response {
        return $this->render('tableau/croise.html.twig', [
            'annee' => $annee,
            'semestres' => $annee->getSemestres()
        ]);
    }

    #[Route('/croise/complet', name: 'croise_complet')]
    public function tableauComplet(
        SemestreRepository $semestreRepository
    ): Response {
        $semestres = $semestreRepository->findByDepartement($this->getDepartement());

        return $this->render('tableau/croise_complet.html.twig', [
            'semestres' => $semestres
        ]);
    }

    #[Route('/validation/{annee}', name: 'validation_sae_ac_annee', requirements: ['annee' => '\d+'])]
    public function validationSaeAc(
        Annee $annee
    ): Response {
        return $this->render('tableau/validation_sae_ac.html.twig', [
            'annee' => $annee,
            'semestres' => $annee->getSemestres()
        ]);
    }

    #[Route('/preconisations/{annee}', name: 'preconisations_annee', requirements: ['annee' => '\d+'])]
    public function tableauPreconisations(
        Annee $annee
    ): Response {
        return $this->render('tableau/preconisations.html.twig', [
            'annee' => $annee,
            'semestres' => $annee->getSemestres()
        ]);
    }

    #[Route('/preconisations/complet', name: 'preconisations_complet')]
    public function tableauPreconisationsComplet(
        SemestreRepository $semestreRepository
    ): Response {
        $semestres = $semestreRepository->findByDepartement($this->getDepartement());

        return $this->render('tableau/preconisations_complet.html.twig', [
            'semestres' => $semestres
        ]);
    }

    public function tableauSemestre(
        ApcSaeCompetenceRepository $apcSaeCompetenceRepository,
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        ApcNiveauRepository $apcNiveauRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Semestre $semestre
    ) {
        $saes = $apcSaeRepository->findBySemestre($semestre);
        $ressources = $apcRessourceRepository->findBySemestre($semestre);

        $compSae = $apcSaeCompetenceRepository->findBySemestre($semestre);
        $compRessources = $apcRessourceCompetenceRepository->findBySemestre($semestre);

        $tab = [];
        $coefficients = [];
        $tab['saes'] = [];
        $tab['ressources'] = [];

        foreach ($saes as $sae) {
            $tab['saes'][$sae->getId()] = [];
            foreach ($sae->getApcSaeApprentissageCritiques() as $ac) {
                $tab['saes'][$sae->getId()][$ac->getApprentissageCritique()->getId()] = $ac;
            }
        }

        foreach ($ressources as $ressource) {
            $tab['ressources'][$ressource->getId()] = [];
            foreach ($ressource->getApcRessourceApprentissageCritiques() as $ac) {
                $tab['ressources'][$ressource->getId()][$ac->getApprentissageCritique()->getId()] = $ac;
            }
        }

        foreach ($compSae as $comp) {
           if (!array_key_exists($comp->getCompetence()->getId(), $coefficients)) {
               $coefficients[$comp->getCompetence()->getId()]['saes'] = [];
               $coefficients[$comp->getCompetence()->getId()]['ressources'] = [];
           }
            $coefficients[$comp->getCompetence()->getId()]['saes'][$comp->getSae()->getId()] = $comp->getCoefficient();
        }

        foreach ($compRessources as $comp) {
            if (!array_key_exists($comp->getCompetence()->getId(), $coefficients)) {
                $coefficients[$comp->getCompetence()->getId()]['saes'] = [];
                $coefficients[$comp->getCompetence()->getId()]['ressources'] = [];
            }
            $coefficients[$comp->getCompetence()->getId()]['ressources'][$comp->getRessource()->getId()] = $comp->getCoefficient();
        }


        return $this->render('tableau/_grilleSemestre.html.twig',
            [
                'semestre' => $semestre,
                'niveaux' => $apcNiveauRepository->findBySemestre($semestre),
                'saes' => $saes,
                'ressources' => $ressources,
                'tab' => $tab,
                'coefficients' => $coefficients
            ]);
    }

    public function tableauValidationAnneeSae(
        ApcSaeRepository $apcSaeRepository,
        Annee $annee
    ) {
        $saes = $apcSaeRepository->findByAnnee($annee);

        $tab = [];
        $tab['saes'] = [];
        $tab['acs'] = [];

        foreach ($saes as $sae) {
            $tab['saes'][$sae->getId()] = [];
            foreach ($sae->getApcSaeApprentissageCritiques() as $ac) {
                $tab['saes'][$sae->getId()][$ac->getApprentissageCritique()->getId()] = $ac;
                $tab['acs'][$ac->getApprentissageCritique()->getId()] = 'ok';
            }
        }

        return $this->render('tableau/_grilleValidation.html.twig',
            [
                'annee' => $annee,
                'niveaux' => $annee->getApcNiveaux(),
                'saes' => $saes,
                'tab' => $tab,
            ]);
    }

    public function tableauPreconisationsSemestre(
        ApcNiveauRepository $apcNiveauRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Semestre $semestre
    ) {
        return $this->render('tableau/_preconisationsSemestre.html.twig',
            [
                'semestre' => $semestre,
                'niveaux' => $apcNiveauRepository->findBySemestre($semestre),
                'saes' => $apcSaeRepository->findBySemestre($semestre),
                'ressources' => $apcRessourceRepository->findBySemestre($semestre),
            ]);
    }

}