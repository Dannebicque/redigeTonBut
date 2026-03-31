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
use App\Repository\AnneeRepository;
use App\Repository\ApcParcoursNiveauRepository;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\SemestreRepository;
use App\Utils\Convert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tableau', name: 'tableau_')]
class TableauController extends BaseController
{
    #[Route('/structure', name: 'structure')]
    public function structure(ApcParcoursRepository $apcParcoursRepository): Response
    {
        $parcours = null;
        if ($this->getDepartement()?->getTypeStructure() === Departement::TYPE3) {
            $parcours = $apcParcoursRepository->findBy(['version' => $this->getVersion()?->getId()]);
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
        if ($parcours instanceof ApcParcours && $this->getDepartement()->getTypeStructure() === Departement::TYPE3) {
            $semestres = $semestreRepository->findByParcours($parcours);
        } else {
            $semestres = $semestreRepository->findByVersion($this->getVersion());
        }

        $json = $structure->setSemestres($semestres)->setVersion($this->getVersion())->getDataJson();

        return $this->json($json);
    }

    #[Route('/api-preconisation/{parcours}', name: 'api_preconisation', options: ['expose' => true])]
    public function apiPreconisation(
        Preconisation $preconisation,
        SemestreRepository $semestreRepository,
        ApcParcours $parcours = null
    ): Response {
        if (!$parcours instanceof ApcParcours) {
            $semestres = $semestreRepository->findByVersion($this->getVersion());
        } else {
            $semestres = $semestreRepository->findByVersionParcours($this->getVersion(), $parcours);
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
        if (!$parcours instanceof ApcParcours) {
            $semestres = $semestreRepository->findByVersion($this->getVersion());
        } else {
            $semestres = $semestreRepository->findByVersionParcours($this->getVersion(), $parcours);
        }

        $json = $volumesHoraires->setSemestres($semestres, $parcours)->getDataJson();

        return $this->json($json);
    }

    #[Route('/api-structure-update/{parcours}', name: 'api_structure_update', options: ['expose' => true])]
    public function apiStructureUpdate(
        SemestreRepository $semestreRepository,
        Request $request,
        ?ApcParcours $parcours = null
    ): JsonResponse {
        if ($this->getVersion()->isVerouilleStructure() === false) {
            $parametersAsArray = [];
            if ($content = $request->getContent()) {
                $parametersAsArray = json_decode($content, true);
            }

            if (!$parcours instanceof ApcParcours) {
                $semestre = $semestreRepository->findSemestre($this->getVersion(), $parametersAsArray['semestre']);
            } else {
                $semestre = $semestreRepository->findSemestreParcours($this->getVersion(),
                    $parametersAsArray['semestre'], $parcours);
            }

            if ($semestre !== null) {
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

        if (!$parcours instanceof ApcParcours || $this->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
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

    #[Route('/croise-comparaison/{annee}/{parcours}', name: 'croise_comparaison_annee', requirements: ['annee' => '\\d+'])]
    public function tableauComparaison(
        SemestreRepository $semestreRepository,
        AnneeRepository $anneeRepository,
        ApcParcoursRepository $apcParcoursRepository,
        TableauCroise $tableauCroise,
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {
        $versionCourante = $this->getVersion();
        $versionPrecedente = $versionCourante?->getPreviousVersion();

        if ($versionPrecedente === null) {
            $this->addFlashBag('warning', 'Aucune version N-1 n\'est definie pour cette version.');

            return $this->redirectToRoute('tableau_croise_annee', [
                'annee' => $annee->getId(),
                'parcours' => $parcours?->getId(),
            ]);
        }

        $anneePrecedente = $anneeRepository->findOneBy([
            'version' => $versionPrecedente,
            'ordre' => $annee->getOrdre(),
        ]);

        $parcoursPrecedent = null;
        if ($parcours instanceof ApcParcours && $this->getDepartement()->getTypeStructure() === Departement::TYPE3) {
            $parcoursPrecedent = $apcParcoursRepository->findOneBy([
                'version' => $versionPrecedente,
                'code' => $parcours->getCode(),
            ]);
        }

        $semestresCourants = (!$parcours instanceof ApcParcours || $this->getDepartement()->getTypeStructure() !== Departement::TYPE3)
            ? $semestreRepository->findBy(['annee' => $annee->getId()])
            : $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);

        $semestresPrecedents = [];
        if ($anneePrecedente instanceof Annee) {
            $semestresPrecedents = (!$parcoursPrecedent instanceof ApcParcours || $this->getDepartement()->getTypeStructure() !== Departement::TYPE3)
                ? $semestreRepository->findBy(['annee' => $anneePrecedente->getId()])
                : $semestreRepository->findBy(['annee' => $anneePrecedente->getId(), 'apcParcours' => $parcoursPrecedent]);
        }

        $semestresCourantsByOrdre = $this->indexSemestresByOrdreLmd($semestresCourants);
        $semestresPrecedentsByOrdre = $this->indexSemestresByOrdreLmd($semestresPrecedents);
        $ordres = array_unique(array_merge(array_keys($semestresCourantsByOrdre), array_keys($semestresPrecedentsByOrdre)));
        sort($ordres);

        $comparaisons = [];
        foreach ($ordres as $ordre) {
            $semestreCourant = $semestresCourantsByOrdre[$ordre] ?? null;
            $semestrePrecedent = $semestresPrecedentsByOrdre[$ordre] ?? null;

            $snapshotCourant = $semestreCourant instanceof Semestre
                ? $this->buildCroiseSnapshot($tableauCroise, $semestreCourant, $parcours)
                : $this->emptyCroiseSnapshot();

            $snapshotPrecedent = $semestrePrecedent instanceof Semestre
                ? $this->buildCroiseSnapshot($tableauCroise, $semestrePrecedent, $parcoursPrecedent)
                : $this->emptyCroiseSnapshot();

            $comparaisons[] = [
                'ordreLmd' => $ordre,
                'semestreCourant' => $semestreCourant,
                'semestrePrecedent' => $semestrePrecedent,
                'snapshotCourant' => $snapshotCourant,
                'snapshotPrecedent' => $snapshotPrecedent,
                'diff' => $this->buildCroiseDiff($snapshotCourant, $snapshotPrecedent),
            ];
        }

        return $this->render('comparaison/tableau/croise.html.twig', [
            'annee' => $annee,
            'anneePrecedente' => $anneePrecedente,
            'parcours' => $parcours,
            'parcoursPrecedent' => $parcoursPrecedent,
            'comparaisons' => $comparaisons,
            'versionCourante' => $versionCourante,
            'versionPrecedente' => $versionPrecedente,
        ]);
    }

    #[Route('/horaire/{annee}/{parcours}', name: 'horaire_annee', requirements: ['annee' => '\d+'])]
    public function tableauH(
        SemestreRepository $semestreRepository,
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {

        if (!$parcours instanceof ApcParcours || $this->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
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
        if (!$parcours instanceof ApcParcours || $this->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
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
    ): Response
    {
        $tableauCroise->getDatas($semestre, $parcours);

        return $this->render('tableau/_grilleSemestre.html.twig',
            [
                'semestre' => $semestre,
                'niveaux' => $tableauCroise->getNiveaux(),
                'saes' => $tableauCroise->getSaes(),
                'saesAl' => $tableauCroise->getSaesAl(),
                'ressources' => $tableauCroise->getRessources(),
                'ressourcesAl' => $tableauCroise->getRessourcesAl(),
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
    ): Response
    {
        if (!$parcours instanceof ApcParcours) {
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
    ): Response
    {

        if (!$parcours instanceof ApcParcours) {
            $niveaux = $annee->getApcNiveaux();
            $saes = $apcSaeRepository->findByAnnee($annee);
        } else {
            $niveaux = $apcParcoursNiveauRepository->findBySemestre($annee->getSemestres()[0], $parcours);
            $saes = $apcSaeParcoursRepository->findByAnnee($annee, $parcours);
        }

        if ($this->getDepartement()->getTypeStructure() === Departement::TYPE3 && $parcours instanceof ApcParcours) {
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
    ): Response
    {
        $tableauPreconisation->getDatas($semestre, $parcours);

        return $this->render('tableau/_preconisationsSemestre.html.twig',
            [
                'semestre' => $semestre,
                'niveaux' => $tableauPreconisation->getNiveaux(),
                'saes' => $tableauPreconisation->getSaes(),
                'saesAl' => $tableauPreconisation->getSaesAl(),
                'ressources' => $tableauPreconisation->getRessources(),
                'ressourcesAl' => $tableauPreconisation->getRessourcesAl(),
            ]);
    }

    private function indexSemestresByOrdreLmd(array $semestres): array
    {
        $indexed = [];
        foreach ($semestres as $semestre) {
            if ($semestre instanceof Semestre) {
                $indexed[$semestre->getOrdreLmd()] = $semestre;
            }
        }

        return $indexed;
    }

    private function emptyCroiseSnapshot(): array
    {
        return [
            'acLabels' => [],
            'acToCompetence' => [],
            'matieres' => [],
            'matiereIds' => ['saes' => [], 'saesAl' => [], 'ressources' => [], 'ressourcesAl' => []],
            'relations' => [],
            'coefficients' => [],
            'counts' => ['matieres' => 0, 'relations' => 0, 'coefficients' => 0],
        ];
    }

    private function buildCroiseSnapshot(TableauCroise $tableauCroise, Semestre $semestre, ?ApcParcours $parcours = null): array
    {
        $tableauCroise->getDatas($semestre, $parcours);

        $snapshot = $this->emptyCroiseSnapshot();
        $acIdToKey = [];
        $competenceIdToKey = [];

        foreach ($tableauCroise->getNiveaux() as $niveau) {
            $competence = $niveau->getCompetence();
            if ($competence === null) {
                continue;
            }

            $competenceKey = (string) $competence->getNomCourt();
            $competenceIdToKey[$competence->getId()] = $competenceKey;

            foreach ($niveau->getApcApprentissageCritiques() as $ac) {
                $acKey = $competenceKey . '::' . $ac->getCode();
                $acIdToKey[$ac->getId()] = $acKey;
                $snapshot['acToCompetence'][$acKey] = $competenceKey;
                $snapshot['acLabels'][$acKey] = $ac->getCode() . ' - ' . $ac->getLibelle();
            }
        }

        $this->appendMatieres($snapshot, $tableauCroise->getSaes(), 'saes', 'SAE');
        $this->appendMatieres($snapshot, $tableauCroise->getSaesAl(), 'saesAl', 'SAE');
        $this->appendMatieres($snapshot, $tableauCroise->getRessources(), 'ressources', 'Ressource');
        $this->appendMatieres($snapshot, $tableauCroise->getRessourcesAl(), 'ressourcesAl', 'Ressource');

        foreach ($tableauCroise->getTab() as $bucket => $rowsByMatiereId) {
            foreach ($rowsByMatiereId as $matiereId => $acRows) {
                $matiereKey = $snapshot['matiereIds'][$bucket][$matiereId] ?? null;
                if ($matiereKey === null) {
                    continue;
                }

                foreach ($acRows as $acId => $value) {
                    $acKey = $acIdToKey[$acId] ?? null;
                    if ($acKey === null) {
                        continue;
                    }

                    $snapshot['relations'][$acKey . '||' . $matiereKey] = true;
                }
            }
        }

        foreach ($tableauCroise->getCoefficients() as $competenceId => $coeffByType) {
            $competenceKey = $competenceIdToKey[$competenceId] ?? null;
            if ($competenceKey === null) {
                continue;
            }

            foreach ($coeffByType as $bucket => $coefficients) {
                foreach ($coefficients as $matiereId => $coefficient) {
                    $matiereKey = $snapshot['matiereIds'][$bucket][$matiereId] ?? null;
                    if ($matiereKey === null) {
                        continue;
                    }

                    $snapshot['coefficients'][$competenceKey . '||' . $matiereKey] = (string) $coefficient;
                }
            }
        }

        $snapshot['counts']['matieres'] = count($snapshot['matieres']);
        $snapshot['counts']['relations'] = count($snapshot['relations']);
        $snapshot['counts']['coefficients'] = count($snapshot['coefficients']);

        return $snapshot;
    }

    private function appendMatieres(array &$snapshot, iterable $matieres, string $bucket, string $type): void
    {
        foreach ($matieres as $matiere) {
            $matiereKey = $type . '|' . $matiere->getCodeMatiere() . '|' . ($matiere->getFicheAdaptationLocale() ? 'AL' : 'NAT');
            $snapshot['matieres'][$matiereKey] = [
                'type' => $type,
                'code' => $matiere->getCodeMatiere(),
                'libelle' => $matiere->getLibelle(),
                'adaptationLocale' => (bool) $matiere->getFicheAdaptationLocale(),
            ];
            $snapshot['matiereIds'][$bucket][$matiere->getId()] = $matiereKey;
        }
    }

    private function buildCroiseDiff(array $snapshotCourant, array $snapshotPrecedent): array
    {
        $relationDiffs = [];
        $relationKeys = array_unique(array_merge(
            array_keys($snapshotCourant['relations']),
            array_keys($snapshotPrecedent['relations'])
        ));

        foreach ($relationKeys as $key) {
            $currentValue = isset($snapshotCourant['relations'][$key]);
            $previousValue = isset($snapshotPrecedent['relations'][$key]);
            if ($currentValue === $previousValue) {
                continue;
            }

            [$acKey, $matiereKey] = explode('||', $key, 2);
            $matiere = $snapshotCourant['matieres'][$matiereKey] ?? $snapshotPrecedent['matieres'][$matiereKey] ?? [
                'type' => '-',
                'code' => $matiereKey,
                'libelle' => '',
                'adaptationLocale' => false,
            ];

            $status = $currentValue ? 'ajout' : 'suppression';
            $relationDiffs[] = [
                'status' => $status,
                'statusLabel' => $status === 'ajout' ? 'Ajout' : 'Suppression',
                'statusClass' => $status === 'ajout' ? 'bg-success' : 'bg-danger',
                'ac' => $snapshotCourant['acLabels'][$acKey] ?? $snapshotPrecedent['acLabels'][$acKey] ?? $acKey,
                'competence' => $snapshotCourant['acToCompetence'][$acKey] ?? $snapshotPrecedent['acToCompetence'][$acKey] ?? '-',
                'matiere' => $matiere,
                'old' => $previousValue,
                'new' => $currentValue,
            ];
        }

        $coefficientDiffs = [];
        $coefficientKeys = array_unique(array_merge(
            array_keys($snapshotCourant['coefficients']),
            array_keys($snapshotPrecedent['coefficients'])
        ));

        foreach ($coefficientKeys as $key) {
            $currentValue = $snapshotCourant['coefficients'][$key] ?? null;
            $previousValue = $snapshotPrecedent['coefficients'][$key] ?? null;

            if ($currentValue === $previousValue) {
                continue;
            }

            [$competence, $matiereKey] = explode('||', $key, 2);
            $matiere = $snapshotCourant['matieres'][$matiereKey] ?? $snapshotPrecedent['matieres'][$matiereKey] ?? [
                'type' => '-',
                'code' => $matiereKey,
                'libelle' => '',
                'adaptationLocale' => false,
            ];

            $status = 'modification';
            $statusLabel = 'Modification';
            $statusClass = 'bg-warning text-dark';
            if ($previousValue === null && $currentValue !== null) {
                $status = 'ajout';
                $statusLabel = 'Ajout';
                $statusClass = 'bg-success';
            } elseif ($previousValue !== null && $currentValue === null) {
                $status = 'suppression';
                $statusLabel = 'Suppression';
                $statusClass = 'bg-danger';
            }

            $coefficientDiffs[] = [
                'status' => $status,
                'statusLabel' => $statusLabel,
                'statusClass' => $statusClass,
                'competence' => $competence,
                'matiere' => $matiere,
                'old' => $previousValue,
                'new' => $currentValue,
            ];
        }

        usort($relationDiffs, static fn(array $a, array $b): int => [$a['matiere']['type'], $a['matiere']['code'], $a['ac']] <=> [$b['matiere']['type'], $b['matiere']['code'], $b['ac']]);
        usort($coefficientDiffs, static fn(array $a, array $b): int => [$a['matiere']['type'], $a['matiere']['code'], $a['competence']] <=> [$b['matiere']['type'], $b['matiere']['code'], $b['competence']]);

        return [
            'relations' => $relationDiffs,
            'coefficients' => $coefficientDiffs,
            'counts' => [
                'relations' => count($relationDiffs),
                'coefficients' => count($coefficientDiffs),
            ],
        ];
    }

}
