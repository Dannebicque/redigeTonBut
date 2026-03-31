<?php

namespace App\Controller;

use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Pdf\PdfManager;
use App\Pdf\PdfSourceType;
use App\Repository\AnneeRepository;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\ApcSaeRessourceRepository;
use App\Repository\SemestreRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/but', name: 'but_')]
class ButController extends BaseController
{
    private const RESSOURCE_DIFF_FIELDS = [
        'libelle',
        'description',
        'motsCles',
        'heuresTotales',
        'tpPpn',
        'cmPreco',
        'tdPreco',
        'tpPreco',
        'ficheAdaptationLocale',
    ];

    private const SAE_DIFF_FIELDS = [
        'libelle',
        'description',
        'objectifs',
        'exemples',
        'heuresTotales',
        'tpPpn',
        'projetPpn',
        'ficheAdaptationLocale',
        'portfolio',
        'stage',
    ];

    public function __construct(
        private readonly PdfManager $pdfManager,
    ) {
    }

    #[Route('/{annee}', name: 'annee', requirements: ['annee' => '\d+'])]
    public function index(Annee $annee): Response
    {
        //todo: filtrer par année...

        return $this->render('but/index.html.twig', [
            'annee' => $annee
        ]);
    }

    #[Route('/ressources-parcours/{annee}', name: 'ressources_annee', requirements: ['annee' => '\d+'])]
    #[Route('/ressources-parcours/{annee}/{parcours}', name: 'ressources_annee', requirements: ['annee' => '\d+'])]
    public function ressources(
        Request $request,
        SemestreRepository $semestreRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository,
        Annee $annee, ApcParcours $parcours = null): Response
    {
        if ($parcours instanceof ApcParcours) {
            $ressources = $apcRessourceParcoursRepository->findByAnneeArray($annee, $parcours);
        } else {
            $ressources = $apcRessourceRepository->findByAnneeArray($annee);
        }

        if ($parcours instanceof ApcParcours && $this->getDepartement()?->getTypeStructure() === Departement::TYPE3) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        }

        [$pdfStatusByRessource, $pendingPdfRessources, $errorPdfRessources] = $this->buildPdfDisplayData(
            $ressources,
            PdfSourceType::RESSOURCE
        );

        return $this->render('but/ressources.html.twig', [
            'ressources' => $ressources,
            'annee' => $annee,
            'selectSemestre' => $request->query->get('semestre'),
            'parcours' => $parcours,
            'semestres' => $semestres,
            'compareMode' => false,
            'pdfStatusByRessource' => $pdfStatusByRessource,
            'pendingPdfRessources' => $pendingPdfRessources,
            'errorPdfRessources' => $errorPdfRessources,
        ]);
    }

    #[Route('/ressources-parcours-comparaison/{annee}/{parcours}', name: 'ressources_annee_comparaison', defaults: ['parcours' => null], requirements: ['annee' => '\\d+'])]
    public function ressourcesComparaison(
        Request $request,
        SemestreRepository $semestreRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository,
        AnneeRepository $anneeRepository,
        ApcParcoursRepository $apcParcoursRepository,
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {
        $versionCourante = $this->getVersion();
        $versionPrecedente = $versionCourante?->getPreviousVersion();

        if ($versionPrecedente === null) {
            $this->addFlashBag('warning', 'Aucune version precedente n\'est definie.');

            return $this->redirectToRoute('but_ressources_annee', [
                'annee' => $annee->getId(),
                'parcours' => $parcours?->getId(),
            ]);
        }

        $anneePrecedente = $anneeRepository->findOneBy([
            'version' => $versionPrecedente,
            'ordre' => $annee->getOrdre(),
        ]);

        if (!$anneePrecedente instanceof Annee) {
            $this->addFlashBag('warning', sprintf('Aucune annee %d dans la version precedente.', $annee->getOrdre()));

            return $this->redirectToRoute('but_ressources_annee', [
                'annee' => $annee->getId(),
                'parcours' => $parcours?->getId(),
            ]);
        }

        $parcoursPrecedent = null;
        if ($parcours instanceof ApcParcours) {
            $parcoursPrecedent = $apcParcoursRepository->findOneBy([
                'version' => $versionPrecedente,
                'code' => $parcours->getCode(),
            ]);
        }

        $ressources = $this->findRessourcesByAnnee(
            $annee,
            $parcours,
            $apcRessourceParcoursRepository,
            $apcRessourceRepository
        );
        $ressourcesPrecedentes = $this->findRessourcesByAnnee(
            $anneePrecedente,
            $parcoursPrecedent,
            $apcRessourceParcoursRepository,
            $apcRessourceRepository
        );

        if ($parcours instanceof ApcParcours && $this->getDepartement()?->getTypeStructure() === Departement::TYPE3) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        }

        [$diffBySemestre, $removedBySemestre] = $this->buildRessourcesDiffBySemestre($ressources, $ressourcesPrecedentes);
        [$pdfStatusByRessource, $pendingPdfRessources, $errorPdfRessources] = $this->buildPdfDisplayData(
            $ressources,
            PdfSourceType::RESSOURCE
        );

        return $this->render('but/ressources.html.twig', [
            'ressources' => $ressources,
            'annee' => $annee,
            'selectSemestre' => $request->query->get('semestre'),
            'parcours' => $parcours,
            'semestres' => $semestres,
            'compareMode' => true,
            'versionPrecedente' => $versionPrecedente,
            'anneePrecedente' => $anneePrecedente,
            'diffBySemestre' => $diffBySemestre,
            'removedBySemestre' => $removedBySemestre,
            'pdfStatusByRessource' => $pdfStatusByRessource,
            'pendingPdfRessources' => $pendingPdfRessources,
            'errorPdfRessources' => $errorPdfRessources,
        ]);
    }

    #[Route('/sae-parcours/{annee}/{parcours}', name: 'sae_annee', requirements: ['annee' => '\d+'])]
    public function saes(
        Request $request,
        SemestreRepository $semestreRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcSaeRepository $apcSaeRepository, Annee $annee, ApcParcours $parcours = null): Response
    {
        if ($parcours instanceof ApcParcours) {
            $saes = $apcSaeParcoursRepository->findByAnneeArray($annee, $parcours);
        } else {
            $saes = $apcSaeRepository->findByAnneeArray($annee);
        }

        if ($this->getDepartement()?->getTypeStructure() === Departement::TYPE3 && $parcours instanceof ApcParcours) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        }

        [$pdfStatusBySae, $pendingPdfSaes, $errorPdfSaes] = $this->buildPdfDisplayData(
            $saes,
            PdfSourceType::SAE
        );

        return $this->render('but/saes.html.twig', [
            'annee' => $annee,
            'saes' => $saes,
            'parcours' => $parcours,
            'selectSemestre' => $request->query->get('semestre'),
            'semestres' => $semestres,
            'compareMode' => false,
            'pdfStatusBySae' => $pdfStatusBySae,
            'pendingPdfSaes' => $pendingPdfSaes,
            'errorPdfSaes' => $errorPdfSaes,
        ]);
    }

    #[Route('/sae-parcours-comparaison/{annee}/{parcours}', name: 'sae_annee_comparaison', defaults: ['parcours' => null], requirements: ['annee' => '\\d+'])]
    public function saesComparaison(
        Request $request,
        SemestreRepository $semestreRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcSaeRepository $apcSaeRepository,
        AnneeRepository $anneeRepository,
        ApcParcoursRepository $apcParcoursRepository,
        Annee $annee,
        ApcParcours $parcours = null
    ): Response {
        $versionCourante = $this->getVersion();
        $versionPrecedente = $versionCourante?->getPreviousVersion();

        if ($versionPrecedente === null) {
            $this->addFlashBag('warning', 'Aucune version precedente n\'est definie.');

            return $this->redirectToRoute('but_sae_annee', [
                'annee' => $annee->getId(),
                'parcours' => $parcours?->getId(),
            ]);
        }

        $anneePrecedente = $anneeRepository->findOneBy([
            'version' => $versionPrecedente,
            'ordre' => $annee->getOrdre(),
        ]);

        if (!$anneePrecedente instanceof Annee) {
            $this->addFlashBag('warning', sprintf('Aucune annee %d dans la version precedente.', $annee->getOrdre()));

            return $this->redirectToRoute('but_sae_annee', [
                'annee' => $annee->getId(),
                'parcours' => $parcours?->getId(),
            ]);
        }

        $parcoursPrecedent = null;
        if ($parcours instanceof ApcParcours) {
            $parcoursPrecedent = $apcParcoursRepository->findOneBy([
                'version' => $versionPrecedente,
                'code' => $parcours->getCode(),
            ]);
        }

        $saes = $this->findSaesByAnnee($annee, $parcours, $apcSaeParcoursRepository, $apcSaeRepository);
        $saesPrecedentes = $this->findSaesByAnnee($anneePrecedente, $parcoursPrecedent, $apcSaeParcoursRepository, $apcSaeRepository);

        if ($this->getDepartement()?->getTypeStructure() === Departement::TYPE3 && $parcours instanceof ApcParcours) {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId(), 'apcParcours' => $parcours]);
        } else {
            $semestres = $semestreRepository->findBy(['annee' => $annee->getId()]);
        }

        [$diffBySemestre, $removedBySemestre] = $this->buildSaesDiffBySemestre($saes, $saesPrecedentes);
        [$pdfStatusBySae, $pendingPdfSaes, $errorPdfSaes] = $this->buildPdfDisplayData(
            $saes,
            PdfSourceType::SAE
        );

        return $this->render('but/saes.html.twig', [
            'annee' => $annee,
            'saes' => $saes,
            'parcours' => $parcours,
            'selectSemestre' => $request->query->get('semestre'),
            'semestres' => $semestres,
            'compareMode' => true,
            'versionPrecedente' => $versionPrecedente,
            'anneePrecedente' => $anneePrecedente,
            'diffBySemestre' => $diffBySemestre,
            'removedBySemestre' => $removedBySemestre,
            'pdfStatusBySae' => $pdfStatusBySae,
            'pendingPdfSaes' => $pendingPdfSaes,
            'errorPdfSaes' => $errorPdfSaes,
        ]);
    }

    #[Route("/fiche-ressource/{apcRessource}", name:"fiche_ressource")]
    public function ficheRessource(
        Request $request,
        ApcSaeRessourceRepository $apcSaeRessourceRepository,
        ApcRessourceRepository $apcRessourceRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        AnneeRepository $anneeRepository,
        ApcParcoursRepository $apcParcoursRepository,
        ApcRessource $apcRessource
    ): Response {
//Todo: vérifier que la ressource ou l'nnée est bien dans la spécialité du connecté (si changement d'id dans l'URL)
        $compareMode = $request->query->getBoolean('compare', false);
        $versionPrecedente = null;
        $ressourceDiff = null;
        $selectedParcours = null;

        if ($apcRessource->getApcRessourceParcours()->count() > 0) {
            $selectedParcours = $apcRessource->getApcRessourceParcours()->first()?->getParcours();
        }

        if ($compareMode) {
            $versionPrecedente = $this->getVersion()?->getPreviousVersion();

            if ($versionPrecedente !== null) {
                $anneeCourante = $apcRessource->getSemestre()?->getAnnee();
                $semestreCourant = $apcRessource->getSemestre();
                $anneePrecedente = $anneeCourante instanceof Annee
                    ? $anneeRepository->findOneBy([
                        'version' => $versionPrecedente,
                        'ordre' => $anneeCourante->getOrdre(),
                    ])
                    : null;

                if ($anneePrecedente instanceof Annee && $semestreCourant !== null) {
                    $parcoursPrecedent = null;
                    if ($selectedParcours instanceof ApcParcours) {
                        $parcoursPrecedent = $apcParcoursRepository->findOneBy([
                            'version' => $versionPrecedente,
                            'code' => $selectedParcours->getCode(),
                        ]);
                    }

                    $ressourcesPrecedentes = $parcoursPrecedent instanceof ApcParcours
                        ? $apcRessourceParcoursRepository->findByAnnee($anneePrecedente, $parcoursPrecedent)
                        : $apcRessourceRepository->findByAnnee($anneePrecedente);

                    $codeRecherche = $this->normalizeRessourceCode($apcRessource->getCodeMatiere());
                    $ressourcePrecedente = null;
                    foreach ($ressourcesPrecedentes as $candidate) {
                        if (
                            $candidate->getSemestre()?->getOrdreLmd() === $semestreCourant->getOrdreLmd()
                            && $this->normalizeRessourceCode($candidate->getCodeMatiere()) === $codeRecherche
                        ) {
                            $ressourcePrecedente = $candidate;
                            break;
                        }
                    }

                    if ($ressourcePrecedente instanceof ApcRessource) {
                        $changes = $this->computeRessourceFieldDiffs($apcRessource, $ressourcePrecedente);
                        $linkedChanges = $this->computeRessourceLinkedDiffs($apcRessource, $ressourcePrecedente);
                        $ressourceDiff = [
                            'status' => (count($changes) > 0 || $this->hasLinkedChanges($linkedChanges)) ? 'changed' : 'unchanged',
                            'changes' => $changes,
                            'linkedChanges' => $linkedChanges,
                            'previous' => $ressourcePrecedente,
                        ];
                    } else {
                        $ressourceDiff = [
                            'status' => 'added',
                            'changes' => [],
                            'linkedChanges' => [
                                'acs' => ['added' => [], 'removed' => []],
                                'prerequis' => ['added' => [], 'removed' => []],
                                'competences' => ['added' => [], 'removed' => []],
                            ],
                            'previous' => null,
                        ];
                    }
                }
            }
        }

        return $this->render('but/ficheRessource.html.twig', [
            'apc_ressource' => $apcRessource,
            'saes' => $apcSaeRessourceRepository->findSaesByRessource($apcRessource),
            'compareMode' => $compareMode,
            'versionPrecedente' => $versionPrecedente,
            'ressourceDiff' => $ressourceDiff,
            'selectedParcours' => $selectedParcours,
        ]);
    }

    #[Route('/fiche-sae/{apcSae}', name: 'fiche_sae')]
    public function ficheSae(
        Request $request,
        ApcSaeRessourceRepository $apcSaeRessourceRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        AnneeRepository $anneeRepository,
        ApcParcoursRepository $apcParcoursRepository,
        ApcSae $apcSae
    ): Response {
        $compareMode = $request->query->getBoolean('compare', false);
        $versionPrecedente = null;
        $saeDiff = null;
        $selectedParcours = null;

        if ($apcSae->getApcSaeParcours()->count() > 0) {
            $selectedParcours = $apcSae->getApcSaeParcours()->first()?->getParcours();
        }

        if ($compareMode) {
            $versionPrecedente = $this->getVersion()?->getPreviousVersion();

            if ($versionPrecedente !== null) {
                $anneeCourante = $apcSae->getSemestre()?->getAnnee();
                $semestreCourant = $apcSae->getSemestre();
                $anneePrecedente = $anneeCourante instanceof Annee
                    ? $anneeRepository->findOneBy([
                        'version' => $versionPrecedente,
                        'ordre' => $anneeCourante->getOrdre(),
                    ])
                    : null;

                if ($anneePrecedente instanceof Annee && $semestreCourant !== null) {
                    $parcoursPrecedent = null;
                    if ($selectedParcours instanceof ApcParcours) {
                        $parcoursPrecedent = $apcParcoursRepository->findOneBy([
                            'version' => $versionPrecedente,
                            'code' => $selectedParcours->getCode(),
                        ]);
                    }

                    $saesPrecedentes = $parcoursPrecedent instanceof ApcParcours
                        ? $apcSaeParcoursRepository->findByAnnee($anneePrecedente, $parcoursPrecedent)
                        : $apcSaeRepository->findByAnnee($anneePrecedente);

                    $codeRecherche = $this->normalizeSaeCode($apcSae->getCodeMatiere());
                    $saePrecedente = null;
                    foreach ($saesPrecedentes as $candidate) {
                        if (
                            $candidate->getSemestre()?->getOrdreLmd() === $semestreCourant->getOrdreLmd()
                            && $this->normalizeSaeCode($candidate->getCodeMatiere()) === $codeRecherche
                        ) {
                            $saePrecedente = $candidate;
                            break;
                        }
                    }

                    if ($saePrecedente instanceof ApcSae) {
                        $changes = $this->computeSaeFieldDiffs($apcSae, $saePrecedente);
                        $linkedChanges = $this->computeSaeLinkedDiffs($apcSae, $saePrecedente);
                        $saeDiff = [
                            'status' => (count($changes) > 0 || $this->hasLinkedChanges($linkedChanges)) ? 'changed' : 'unchanged',
                            'changes' => $changes,
                            'linkedChanges' => $linkedChanges,
                            'previous' => $saePrecedente,
                        ];
                    } else {
                        $saeDiff = [
                            'status' => 'added',
                            'changes' => [],
                            'linkedChanges' => [
                                'acs' => ['added' => [], 'removed' => []],
                                'ressources' => ['added' => [], 'removed' => []],
                                'competences' => ['added' => [], 'removed' => []],
                            ],
                            'previous' => null,
                        ];
                    }
                }
            }
        }

        return $this->render('but/ficheSae.html.twig', [
            'apc_sae' => $apcSae,
            'ressources' => $apcSaeRessourceRepository->findRessourcesBySae($apcSae),
            'compareMode' => $compareMode,
            'versionPrecedente' => $versionPrecedente,
            'saeDiff' => $saeDiff,
            'selectedParcours' => $selectedParcours,
        ]);
    }

    private function findRessourcesByAnnee(
        Annee $annee,
        ?ApcParcours $parcours,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository
    ): array {
        if ($parcours instanceof ApcParcours) {
            return $apcRessourceParcoursRepository->findByAnneeArray($annee, $parcours);
        }

        return $apcRessourceRepository->findByAnneeArray($annee);
    }

    private function findSaesByAnnee(
        Annee $annee,
        ?ApcParcours $parcours,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcSaeRepository $apcSaeRepository
    ): array {
        if ($parcours instanceof ApcParcours) {
            return $apcSaeParcoursRepository->findByAnneeArray($annee, $parcours);
        }

        return $apcSaeRepository->findByAnneeArray($annee);
    }

    /**
     * @return array{0: array<string, array{status: string, errorMessage: ?string}>, 1: array<int, array{code: string, libelle: string, errorMessage: ?string}>, 2: array<int, array{code: string, libelle: string, errorMessage: ?string}>}
     */
    private function buildPdfDisplayData(array $itemsBySemestre, PdfSourceType $sourceType): array
    {
        $itemsById = [];
        foreach ($itemsBySemestre as $items) {
            foreach ($items as $item) {
                $itemId = (string) $item->getId();
                $itemsById[$itemId] = $item;
            }
        }

        $statusesById = $this->pdfManager->getDisplayStatusesForSources($sourceType, array_keys($itemsById));
        $pendingItems = [];
        $errorItems = [];

        foreach ($itemsById as $itemId => $item) {
            $statusData = $statusesById[$itemId] ?? [
                'status' => PdfManager::DISPLAY_STATUS_ABSENT,
                'errorMessage' => null,
            ];

            if ($statusData['status'] === PdfManager::DISPLAY_STATUS_PENDING) {
                $pendingItems[] = [
                    'code' => (string) $item->getCodeMatiere(),
                    'libelle' => (string) $item->getLibelle(),
                    'errorMessage' => null,
                ];
            }

            if ($statusData['status'] === PdfManager::DISPLAY_STATUS_ERROR) {
                $errorItems[] = [
                    'code' => (string) $item->getCodeMatiere(),
                    'libelle' => (string) $item->getLibelle(),
                    'errorMessage' => $statusData['errorMessage'],
                ];
            }
        }

        return [$statusesById, $pendingItems, $errorItems];
    }

    private function buildRessourcesDiffBySemestre(array $currentBySemestre, array $previousBySemestre): array
    {
        $diffBySemestre = [];
        $removedBySemestre = [];

        $semestres = array_unique(array_merge(array_keys($currentBySemestre), array_keys($previousBySemestre)));
        sort($semestres);

        foreach ($semestres as $ordreLmd) {
            $currentByCode = $this->indexRessourcesByCode($currentBySemestre[$ordreLmd] ?? []);
            $previousByCode = $this->indexRessourcesByCode($previousBySemestre[$ordreLmd] ?? []);

            $allCodes = array_unique(array_merge(array_keys($currentByCode), array_keys($previousByCode)));
            sort($allCodes);

            $diffBySemestre[$ordreLmd] = [];
            $removedBySemestre[$ordreLmd] = [];

            foreach ($allCodes as $code) {
                $current = $currentByCode[$code] ?? null;
                $previous = $previousByCode[$code] ?? null;

                if ($current !== null && $previous !== null) {
                    $changes = $this->computeRessourceFieldDiffs($current, $previous);
                    $linkedChanges = $this->computeRessourceLinkedDiffs($current, $previous);
                    $status = (count($changes) > 0 || $this->hasLinkedChanges($linkedChanges)) ? 'changed' : 'unchanged';
                    $diffBySemestre[$ordreLmd][$code] = [
                        'status' => $status,
                        'changes' => $changes,
                        'linkedChanges' => $linkedChanges,
                        'previous' => $previous,
                    ];
                    continue;
                }

                if ($current !== null) {
                    $diffBySemestre[$ordreLmd][$code] = [
                        'status' => 'added',
                        'changes' => [],
                        'linkedChanges' => [
                            'acs' => ['added' => [], 'removed' => []],
                            'prerequis' => ['added' => [], 'removed' => []],
                            'competences' => ['added' => [], 'removed' => []],
                        ],
                        'previous' => null,
                    ];
                    continue;
                }

                if ($previous !== null) {
                    $removedBySemestre[$ordreLmd][$code] = $previous;
                }
            }
        }

        return [$diffBySemestre, $removedBySemestre];
    }

    private function buildSaesDiffBySemestre(array $currentBySemestre, array $previousBySemestre): array
    {
        $diffBySemestre = [];
        $removedBySemestre = [];

        $semestres = array_unique(array_merge(array_keys($currentBySemestre), array_keys($previousBySemestre)));
        sort($semestres);

        foreach ($semestres as $ordreLmd) {
            $currentByCode = $this->indexSaesByCode($currentBySemestre[$ordreLmd] ?? []);
            $previousByCode = $this->indexSaesByCode($previousBySemestre[$ordreLmd] ?? []);

            $allCodes = array_unique(array_merge(array_keys($currentByCode), array_keys($previousByCode)));
            sort($allCodes);

            $diffBySemestre[$ordreLmd] = [];
            $removedBySemestre[$ordreLmd] = [];

            foreach ($allCodes as $code) {
                $current = $currentByCode[$code] ?? null;
                $previous = $previousByCode[$code] ?? null;

                if ($current !== null && $previous !== null) {
                    $changes = $this->computeSaeFieldDiffs($current, $previous);
                    $linkedChanges = $this->computeSaeLinkedDiffs($current, $previous);
                    $status = (count($changes) > 0 || $this->hasLinkedChanges($linkedChanges)) ? 'changed' : 'unchanged';
                    $diffBySemestre[$ordreLmd][$code] = [
                        'status' => $status,
                        'changes' => $changes,
                        'linkedChanges' => $linkedChanges,
                        'previous' => $previous,
                    ];
                    continue;
                }

                if ($current !== null) {
                    $diffBySemestre[$ordreLmd][$code] = [
                        'status' => 'added',
                        'changes' => [],
                        'linkedChanges' => [
                            'acs' => ['added' => [], 'removed' => []],
                            'ressources' => ['added' => [], 'removed' => []],
                            'competences' => ['added' => [], 'removed' => []],
                        ],
                        'previous' => null,
                    ];
                    continue;
                }

                if ($previous !== null) {
                    $removedBySemestre[$ordreLmd][$code] = $previous;
                }
            }
        }

        return [$diffBySemestre, $removedBySemestre];
    }

    private function indexRessourcesByCode(array $ressources): array
    {
        $indexed = [];
        foreach ($ressources as $ressource) {
            $key = $this->normalizeRessourceCode($ressource->getCodeMatiere());
            if ($key === '') {
                continue;
            }

            $indexed[$key] = $ressource;
        }

        return $indexed;
    }

    private function indexSaesByCode(array $saes): array
    {
        $indexed = [];
        foreach ($saes as $sae) {
            $key = $this->normalizeSaeCode($sae->getCodeMatiere());
            if ($key === '') {
                continue;
            }

            $indexed[$key] = $sae;
        }

        return $indexed;
    }

    private function computeRessourceFieldDiffs(object $current, object $previous): array
    {
        $changes = [];
        foreach (self::RESSOURCE_DIFF_FIELDS as $field) {
            $currentValue = $this->extractRessourceFieldValue($current, $field);
            $previousValue = $this->extractRessourceFieldValue($previous, $field);

            if ($currentValue !== $previousValue) {
                $changes[$field] = [
                    'old' => $previousValue,
                    'new' => $currentValue,
                ];
            }
        }

        return $changes;
    }

    private function computeSaeFieldDiffs(ApcSae $current, ApcSae $previous): array
    {
        $changes = [];
        foreach (self::SAE_DIFF_FIELDS as $field) {
            $currentValue = $this->extractSaeFieldValue($current, $field);
            $previousValue = $this->extractSaeFieldValue($previous, $field);

            if ($currentValue !== $previousValue) {
                $changes[$field] = [
                    'old' => $previousValue,
                    'new' => $currentValue,
                ];
            }
        }

        return $changes;
    }

    private function extractRessourceFieldValue(object $ressource, string $field): mixed
    {
        if ($field === 'ficheAdaptationLocale') {
            return $ressource->getFicheAdaptationLocale();
        }

        $getter = 'get' . ucfirst($field);
        return method_exists($ressource, $getter) ? $ressource->{$getter}() : null;
    }

    private function extractSaeFieldValue(ApcSae $sae, string $field): mixed
    {
        $getter = 'get' . ucfirst($field);
        return method_exists($sae, $getter) ? $sae->{$getter}() : null;
    }

    private function normalizeRessourceCode(?string $code): string
    {
        return mb_strtoupper(trim((string) $code));
    }

    private function normalizeSaeCode(?string $code): string
    {
        return mb_strtoupper(trim((string) $code));
    }

    private function computeRessourceLinkedDiffs(ApcRessource $current, ApcRessource $previous): array
    {
        return [
            'acs' => $this->buildSetDiff(
                $this->extractAcsFromRessource($current),
                $this->extractAcsFromRessource($previous)
            ),
            'prerequis' => $this->buildSetDiff(
                $this->extractPrerequisFromRessource($current),
                $this->extractPrerequisFromRessource($previous)
            ),
            'competences' => $this->buildSetDiff(
                $this->extractCompetencesFromRessource($current),
                $this->extractCompetencesFromRessource($previous)
            ),
        ];
    }

    private function computeSaeLinkedDiffs(ApcSae $current, ApcSae $previous): array
    {
        return [
            'acs' => $this->buildSetDiff(
                $this->extractAcsFromSae($current),
                $this->extractAcsFromSae($previous)
            ),
            'ressources' => $this->buildSetDiff(
                $this->extractRessourcesFromSae($current),
                $this->extractRessourcesFromSae($previous)
            ),
            'competences' => $this->buildSetDiff(
                $this->extractCompetencesFromSae($current),
                $this->extractCompetencesFromSae($previous)
            ),
        ];
    }

    private function extractAcsFromRessource(ApcRessource $ressource): array
    {
        $acs = [];
        foreach ($ressource->getApcRessourceApprentissageCritiques() as $link) {
            $ac = $link->getApprentissageCritique();
            if ($ac === null) {
                continue;
            }

            $acs[] = trim($ac->getCode() . ' | ' . $ac->getLibelle());
        }

        return $acs;
    }

    private function extractPrerequisFromRessource(ApcRessource $ressource): array
    {
        $prerequis = [];
        foreach ($ressource->getRessourcesPreRequises() as $pre) {
            $prerequis[] = trim($pre->getCodeMatiere() . ' | ' . $pre->getLibelle());
        }

        return $prerequis;
    }

    private function extractCompetencesFromRessource(ApcRessource $ressource): array
    {
        $competences = [];
        foreach ($ressource->getApcRessourceCompetences() as $link) {
            $competence = $link->getCompetence();
            if ($competence === null) {
                continue;
            }

            $competences[] = trim($competence->getNomCourt() . ' | ' . $competence->getLibelle());
        }

        return $competences;
    }

    private function extractAcsFromSae(ApcSae $sae): array
    {
        $acs = [];
        foreach ($sae->getApcSaeApprentissageCritiques() as $link) {
            $ac = $link->getApprentissageCritique();
            if ($ac === null) {
                continue;
            }

            $acs[] = trim($ac->getCode() . ' | ' . $ac->getLibelle());
        }

        return $acs;
    }

    private function extractRessourcesFromSae(ApcSae $sae): array
    {
        $ressources = [];
        foreach ($sae->getApcSaeRessources() as $link) {
            $ressource = $link->getRessource();
            if ($ressource === null) {
                continue;
            }

            $ressources[] = trim($ressource->getCodeMatiere() . ' | ' . $ressource->getLibelle());
        }

        return $ressources;
    }

    private function extractCompetencesFromSae(ApcSae $sae): array
    {
        $competences = [];
        foreach ($sae->getApcSaeCompetences() as $link) {
            $competence = $link->getCompetence();
            if ($competence === null) {
                continue;
            }

            $competences[] = trim($competence->getNomCourt() . ' | ' . $competence->getLibelle());
        }

        return $competences;
    }

    private function buildSetDiff(array $currentValues, array $previousValues): array
    {
        $current = array_values(array_unique(array_filter(array_map('trim', $currentValues))));
        $previous = array_values(array_unique(array_filter(array_map('trim', $previousValues))));
        sort($current);
        sort($previous);

        return [
            'added' => array_values(array_diff($current, $previous)),
            'removed' => array_values(array_diff($previous, $current)),
        ];
    }

    private function hasLinkedChanges(array $linkedChanges): bool
    {
        foreach ($linkedChanges as $diff) {
            if (($diff['added'] ?? []) !== [] || ($diff['removed'] ?? []) !== []) {
                return true;
            }
        }

        return false;
    }
}
