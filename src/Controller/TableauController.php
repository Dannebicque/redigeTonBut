<?php

namespace App\Controller;

use App\Classes\Apc\TableauCroise;
use App\Classes\Apc\TableauPreconisation;
use App\Classes\Tableau\Preconisation;
use App\Classes\Tableau\Structure;
use App\Classes\Tableau\VolumesHoraires;
use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Repository\ApcParcoursNiveauRepository;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
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
    public function structure(ApcParcoursRepository $apcParcoursRepository): Response
    {
        $parcours = null;
        if ($this->getDepartement()->getTypeStructure() === Departement::TYPE3) {
            $parcours = $apcParcoursRepository->findBy(['departement' => $this->getDepartement()->getId()]);
        }

        return $this->render('tableau/structure.html.twig', [
            'parcours' => $parcours
        ]);
    }

    #[Route('/api-structure/{parcours}', name: 'api_structure', options: ["expose" => true])]
    public function apiStructure(
        Structure $structure,
        SemestreRepository $semestreRepository,
        ?ApcParcours $parcours = null
    ): Response {
        if ($parcours !== null && $this->getDepartement()->getTypeStructure() === Departement::TYPE3) {
            $semestres = $semestreRepository->findByParcours($parcours);
        } else {
            $semestres = $semestreRepository->findByDepartement($this->getDepartement());
        }

        $json = $structure->setSemestres($semestres)->setDepartement($this->getDepartement())->getDataJson();

        return $this->json($json);
    }

    #[Route('/api-preconisation/{parcours}', name: 'api_preconisation', options: ['expose' => true])]
    public function apiPreconisation(
        Preconisation $preconisation,
        SemestreRepository $semestreRepository,
        ApcParcours $parcours = null
    ): Response {
        if ($parcours === null) {
            $semestres = $semestreRepository->findByDepartement($this->getDepartement());
        } else {
            $semestres = $semestreRepository->findByDepartementParcours($this->getDepartement(), $parcours);
        }

        $json = $preconisation->setSemestresCompetences($semestres, $parcours)->getDataJson();

        return $this->json($json);
    }

    #[Route('/api-volumes-horaires/{parcours}', name: 'api_volumes_horaires', options: ['expose' => true])]
    public function apiVolumesHoraires(
        VolumesHoraires $volumesHoraires,
        SemestreRepository $semestreRepository,
        ApcParcours $parcours = null
    ): Response {
        if ($parcours === null) {
            $semestres = $semestreRepository->findByDepartement($this->getDepartement());
        } else {
            $semestres = $semestreRepository->findByDepartementParcours($this->getDepartement(), $parcours);
        }

        $json = $volumesHoraires->setSemestres($semestres, $parcours)->getDataJson();

        return $this->json($json);
    }

    #[Route('/api-structure-update/{parcours}', name: 'api_structure_update', options: ['expose' => true])]
    public function apiStructureUpdate(
        SemestreRepository $semestreRepository,
        Request $request,
        ?ApcParcours $parcours = null
    ) {
        if ($this->getDepartement()->getVerouilleStructure() === false) {
            $parametersAsArray = [];
            if ($content = $request->getContent()) {
                $parametersAsArray = json_decode($content, true);
            }

            if ($parcours === null) {
                $semestre = $semestreRepository->findSemestre($this->getDepartement(), $parametersAsArray['semestre']);
            } else {
                $semestre = $semestreRepository->findSemestreParcours($this->getDepartement(),
                    $parametersAsArray['semestre'], $parcours);
            }
            if ($semestre !== null) {//todo: et vériifer lien semestre/département

                switch ($parametersAsArray['champ']) {
                    case 'nbHeuresRessourcesSae':
                        $semestre->setNbHeuresRessourceSae(Convert::convertToFloat($parametersAsArray['valeur']));
                        break;
                    case 'pourcentageAdaptationLocale':
                        $pourcentage = $parametersAsArray['valeur'];
                        $semestre->setPourcentageAdaptationLocale(ceil($pourcentage));
                        //mise à jour du volume horaire
                        $calcul = $semestre->getNbHeuresRessourceSae() * $pourcentage / 100;
                        $semestre->setNbHeuresEnseignementLocale(ceil($calcul));
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
                        //mise à jour du pourcentage
                        $calcul = $semestre->getNbHeuresEnseignementLocale() / $semestre->getNbHeuresRessourceSae() * 100;
                        $semestre->setPourcentageAdaptationLocale(number_format(Convert::convertToFloat($calcul), 2));
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
                    case 'nbSemaines':
                        $semestre->setNbSemaines(Convert::convertToFloat($parametersAsArray['valeur']));
                        break;
                    case 'nbSemainesConges':
                        $semestre->setNbSemainesConges(Convert::convertToFloat($parametersAsArray['valeur']));
                        break;
                    case 'nbDemiJournees':
                        $semestre->setNbDemiJournees(Convert::convertToFloat($parametersAsArray['valeur']));
                        break;
                    case 'nbHeuresTpNational':
                        $semestre->setNbHeuresTpNational(Convert::convertToFloat($parametersAsArray['valeur']));
                        break;
                    case 'nbHeuresTpLocale':
                        $semestre->setNbHeuresTpLocale(Convert::convertToFloat($parametersAsArray['valeur']));
                        break;
                }

                $this->entityManager->flush();

                return $this->json($parametersAsArray);
            }
        }

        return $this->json(false);
    }

    #[Route('/croise/{annee}/{parcours}', name: 'croise_annee', requirements: ['annee' => '\d+'])]
    public function tableau(
        SemestreRepository $semestreRepository,
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {

        if ($parcours === null || $this->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        }

        return $this->render('tableau/croise.html.twig', [
            'parcours' => $parcours,
            'annee' => $annee,
            'semestres' => $semestres
        ]);
    }

    #[Route('/horaire/{annee}/{parcours}', name: 'horaire_annee', requirements: ['annee' => '\d+'])]
    public function tableauH(
        SemestreRepository $semestreRepository,
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {

        if ($parcours === null || $this->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        }

        return $this->render('tableau/horaire.html.twig', [
            'parcours' => $parcours,
            'annee' => $annee,
            'semestres' => $semestres
        ]);
    }

    #[Route('/validation/{annee}/{parcours}', name: 'validation_sae_ac_annee', requirements: ['annee' => '\d+'])]
    public function validationSaeAc(
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {
        return $this->render('tableau/validation_sae_ac.html.twig', [
            'annee' => $annee,
            'parcours' => $parcours,
            'semestres' => $annee->getSemestres()
        ]);
    }

    #[Route('/preconisations/{annee}/{parcours}', name: 'preconisations_annee', requirements: ['annee' => '\d+'])]
    public function tableauPreconisations(
        SemestreRepository $semestreRepository,
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {
        if ($parcours === null || $this->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        }

        return $this->render('tableau/preconisations.html.twig', [
            'parcours' => $parcours,
            'annee' => $annee,
            'semestres' => $semestres,
        ]);
    }

    public function tableauSemestre(
        TableauCroise $tableauCroise,
        Semestre $semestre,
        ?ApcParcours $parcours = null
    ) {
        $tableauCroise->getDatas($semestre, $parcours);

        return $this->render('tableau/_grilleSemestre.html.twig',
            [
                'semestre' => $semestre,
                'niveaux' => $tableauCroise->getNiveaux(),
                'saes' => $tableauCroise->getSaes(),
                'ressources' => $tableauCroise->getRessources(),
                'tab' => $tableauCroise->getTab(),
                'coefficients' => $tableauCroise->getCoefficients()
            ]);
    }


    public function tableauHoraire(
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
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

        return $this->render('tableau/_grilleHoraire.html.twig',
            [
                'semestre' => $semestre,
                'saes' => $saes,
                'ressources' => $ressources,
            ]);
    }

    public function tableauValidationAnneeSae(
        SemestreRepository $semestreRepository,
        ApcParcoursNiveauRepository $apcParcoursNiveauRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcSaeRepository $apcSaeRepository,
        Annee $annee,
        ApcParcours $parcours = null
    ) {

        if ($parcours === null) {
            $niveaux = $annee->getApcNiveaux();
            $saes = $apcSaeRepository->findByAnnee($annee);
        } else {
            $niveaux = $apcParcoursNiveauRepository->findBySemestre($annee->getSemestres()[0], $parcours);
            $saes = $apcSaeParcoursRepository->findByAnnee($annee, $parcours);
        }

        if ($this->getDepartement()->getTypeStructure() === Departement::TYPE3 && $parcours !== null) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        }

        $tSaeSemestre = [];
        foreach ($annee->getSemestres() as $sem) {
            $tSaeSemestre[$sem->getOrdreLmd()] = [];
        }
        foreach ($saes as $sae) {
            if ($sae->getFicheAdaptationLocale() === false) {
                $tSaeSemestre[$sae->getSemestre()->getOrdreLmd()][] = $sae;
            }
        }

        $tab = [];
        $tab['saes'] = [];
        $tab['acs'] = [];

        foreach ($saes as $sae) {
            if ($sae->getFicheAdaptationLocale() === false) {
                $tab['saes'][$sae->getId()] = [];
                foreach ($sae->getApcSaeApprentissageCritiques() as $ac) {
                    $tab['saes'][$sae->getId()][$ac->getApprentissageCritique()->getId()] = $ac;
                    $tab['acs'][$ac->getApprentissageCritique()->getId()] = 'ok';
                }
            }
        }

        return $this->render('tableau/_grilleValidation.html.twig',
            [
                'annee' => $annee,
                'niveaux' => $niveaux,
                'saes' => $saes,
                'tab' => $tab,
                'tSaeSemestre' => $tSaeSemestre,
                'semestres' => $semestres
            ]);
    }

    public function tableauPreconisationsSemestre(
        TableauPreconisation $tableauPreconisation,
        Semestre $semestre,
        ApcParcours $parcours = null,
    ) {
        $tableauPreconisation->getDatas($semestre, $parcours);

        return $this->render('tableau/_preconisationsSemestre.html.twig',
            [
                'semestre' => $semestre,
                'niveaux' => $tableauPreconisation->getNiveaux(),
                'saes' => $tableauPreconisation->getSaes(),
                'ressources' => $tableauPreconisation->getRessources(),
            ]);
    }

}
