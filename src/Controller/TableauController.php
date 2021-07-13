<?php

namespace App\Controller;

use App\Classes\Tableau\Preconisation;
use App\Classes\Tableau\Structure;
use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\Semestre;
use App\Repository\ApcComptenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcParcoursNiveauRepository;
use App\Repository\ApcRessourceCompetenceRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeCompetenceRepository;
use App\Repository\ApcSaeParcoursRepository;
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
    public function structure(
    ): Response
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
        $json = $structure->setSemestres($semestres)->setDepartement($this->getDepartement())->getDataJson();

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
                case 'nbSemainesStageMin':
                    $semestre->setNbSemaineStageMin(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbSemainesStageMax':
                    $semestre->setNbSemainesStageMax(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbHeuresProjet':
                    $semestre->setNbHeuresProjet(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                    case 'nbHeuresEnseignementLocale':
                    $semestre->setNbHeuresEnseignementLocale(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbHeuresEnseignementSaeLocale':
                    $semestre->setNbHeuresEnseignementSaeLocale(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbHeuresEnseignementRessourceLocale':
                    $semestre->setNbHeuresEnseignementRessourceLocale(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
                case 'nbHeuresEnseignementRessourceNational':
                    $semestre->setNbHeuresEnseignementRessourceNational(Convert::convertToFloat($parametersAsArray['valeur']));
                    break;
            }

            $this->entityManager->flush();

            return $this->json($parametersAsArray);
        }

        return $this->json(false);
    }

    #[Route('/croise/{annee}/{parcours}', name: 'croise_annee', requirements: ['annee' => '\d+'])]
    public function tableau(
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {

        return $this->render('tableau/croise.html.twig', [
            'parcours' => $parcours,
            'annee' => $annee,
            'semestres' => $annee->getSemestres()
        ]);
    }

    #[Route('/horaire/{annee}/{parcours}', name: 'horaire_annee', requirements: ['annee' => '\d+'])]
    public function tableauH(
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {

        return $this->render('tableau/horaire.html.twig', [
            'parcours' => $parcours,
            'annee' => $annee,
            'semestres' => $annee->getSemestres()
        ]);
    }

//    #[Route('/croise/complet', name: 'croise_complet')]
//    public function tableauComplet(
//        SemestreRepository $semestreRepository
//    ): Response {
//        $semestres = $semestreRepository->findByDepartement($this->getDepartement());
//
//        return $this->render('tableau/croise_complet.html.twig', [
//            'semestres' => $semestres
//        ]);
//    }

    #[Route('/validation/{annee}', name: 'validation_sae_ac_annee', requirements: ['annee' => '\d+'])]
    public function validationSaeAc(
        Annee $annee
    ): Response {
        return $this->render('tableau/validation_sae_ac.html.twig', [
            'annee' => $annee,
            'semestres' => $annee->getSemestres()
        ]);
    }

    #[Route('/preconisations/{annee}/{parcours}', name: 'preconisations_annee', requirements: ['annee' => '\d+'])]
    public function tableauPreconisations(
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {
        $semestres = $annee->getSemestres();

        return $this->render('tableau/preconisations.html.twig', [
            'parcours' => $parcours,
            'annee' => $annee,
            'semestres' => $semestres,
        ]);
    }

//    #[Route('/preconisations/complet', name: 'preconisations_complet')]
//    public function tableauPreconisationsComplet(
//        SemestreRepository $semestreRepository
//    ): Response {
//        $semestres = $semestreRepository->findByDepartement($this->getDepartement());
//
//        return $this->render('tableau/preconisations_complet.html.twig', [
//            'semestres' => $semestres
//        ]);
//    }

    public function tableauSemestre(
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcSaeCompetenceRepository $apcSaeCompetenceRepository,
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        ApcNiveauRepository $apcNiveauRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Semestre $semestre,
        ?ApcParcours $parcours = null
    ) {
        if ($parcours === null) {
            $saes = $apcSaeRepository->findBySemestre($semestre);
            $ressources = $apcRessourceRepository->findBySemestre($semestre);
        } else {
            $saes = $apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
            $ressources = $apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
        }


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


    public function tableauHoraire(
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcSaeCompetenceRepository $apcSaeCompetenceRepository,
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        ApcNiveauRepository $apcNiveauRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Semestre $semestre,
        ?ApcParcours $parcours = null
    ) {
        if ($parcours === null) {
            $saes = $apcSaeRepository->findBySemestre($semestre);
            $ressources = $apcRessourceRepository->findBySemestre($semestre);
        } else {
            $saes = $apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
            $ressources = $apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
        }


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


        return $this->render('tableau/_grilleHoraire.html.twig',
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
        ApcSaeRepository $apcSaeRepository,
        ApcParcoursNiveauRepository $apcParcoursNiveauRepository,
        ApcRessourceRepository $apcRessourceRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcNiveauRepository $apcNiveauRepository,
        Semestre $semestre,
        ApcParcours $apcParcours = null,
    ) {

        if ($apcParcours === null) {

                $saes = $apcSaeRepository->findBySemestre($semestre);
                $ressources= $apcRessourceRepository->findBySemestre($semestre);
                 $niveaux =$apcNiveauRepository->findBySemestre($semestre);
        } else {
                $saes = $apcSaeParcoursRepository->findBySemestre($semestre, $apcParcours);
                $ressources = $apcRessourceParcoursRepository->findBySemestre($semestre, $apcParcours);
                $niveaux = $apcParcoursNiveauRepository->findBySemestre($semestre, $apcParcours); //todo: a affiner...

        }

        return $this->render('tableau/_preconisationsSemestre.html.twig',
            [
                'semestre' => $semestre,
                'niveaux' => $niveaux,
                'saes' => $saes,
                'ressources' => $ressources,
            ]);
    }

}
