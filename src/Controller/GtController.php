<?php

namespace App\Controller;

use App\Entity\Departement;
use App\Entity\Version;
use App\Pdf\Builder\ReferentielPdfPayloadBuilder;
use App\Pdf\PdfManager;
use App\Pdf\PdfSourceType;
use App\Repository\DepartementRepository;
use App\Repository\SemestreRepository;
use App\Classes\Tableau\VolumesHoraires;
use App\Entity\ApcParcours;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_GT')]
class GtController extends BaseController
{
    public function __construct(
        private readonly PdfManager $pdfManager
    ) {
    }

    #[Route('/gt', name: 'gt_index')]
    public function index(
        DepartementRepository $departementRepository
    ): Response
    {
        $departements = $departementRepository->findAll();
        $currentDepartement = $this->getDepartement();

        $parcours = [];
        if ($currentDepartement instanceof Departement) {
            $version = $this->getVersion();
            if ($version) {
                $parcours = $version->getApcParcours();
            }
        } else {
            $currentDepartement = null;
        }

        return $this->render('gt/index.html.twig', [
            'departements' => $departements,
            'currentDepartement' => $currentDepartement ,
            'pdfReferentielStatuses' => $this->buildReferentielPdfStatuses($version),
            'parcours' => $parcours,
        ]);
    }

    private function buildReferentielPdfStatuses(?Version $version): array
    {
        $defaultStatus = [
            'status' => PdfManager::DISPLAY_STATUS_ABSENT,
            'errorMessage' => null,
            'lastGeneratedAt' => null,
        ];

        if ($version === null || $version->getId() === null) {
            return [
                'complete' => $defaultStatus,
            ];
        }

        $sourceId = (string) $version->getId();

        $statuses = $this->pdfManager->getDisplayStatusesForSources(
            PdfSourceType::REFERENTIEL,
            [$sourceId],
            ReferentielPdfPayloadBuilder::DOCUMENT_KEY_COMPLETE,
        );

        return [
            'complete' => $statuses[$sourceId] ?? $defaultStatus,
        ];
     }

    #[Route('/gt/synthese-heures/{parcours}', name: 'gt_synthese_heures', defaults: ['parcours' => null])]
    public function syntheseHeures(
        SemestreRepository $semestreRepository,
        VolumesHoraires $volumesHoraires,
        ?ApcParcours $parcours = null
    ): Response
    {
        $currentDepartement = $this->getDepartement();
        if ($currentDepartement instanceof RedirectResponse) {
            return $currentDepartement;
        }

        $version = $this->getVersion();
        if ($version === null) {
            $this->addFlashBag('danger', 'Aucune version active.');
            return $this->redirectToRoute('gt_index');
        }

        $allParcours = $version->getApcParcours();
        if ($parcours === null && count($allParcours) > 0) {
            $parcours = $allParcours[0];
        }


        $semestres = $semestreRepository->findByVersion($version);

        if ($parcours instanceof ApcParcours) {
            $filteredSemestres = [];
            foreach ($semestres as $semestre) {
                if ($semestre->getApcParcours() === null || $semestre->getApcParcours()->getId() === $parcours->getId()) {
                    $filteredSemestres[] = $semestre;
                }
            }
            $semestres = $filteredSemestres;
        }

        $donnees = $volumesHoraires->setSemestres($semestres, $parcours)->getDataJson();

        $semestresData = [];
        $yearsData = [
            1 => ['label' => 'B.U.T. 1', 'semestres' => [1, 2], 'ressources' => 0.0, 'sae' => 0.0, 'adaptation' => 0.0, 'total' => 0.0, 'projet' => 0.0, 'totalAvecProjet' => 0.0, 'tp_ressources' => 0.0, 'tp_adaptation' => 0.0, 'tp_total' => 0.0],
            2 => ['label' => 'B.U.T. 2', 'semestres' => [3, 4], 'ressources' => 0.0, 'sae' => 0.0, 'adaptation' => 0.0, 'total' => 0.0, 'projet' => 0.0, 'totalAvecProjet' => 0.0, 'tp_ressources' => 0.0, 'tp_adaptation' => 0.0, 'tp_total' => 0.0],
            3 => ['label' => 'B.U.T. 3', 'semestres' => [5, 6], 'ressources' => 0.0, 'sae' => 0.0, 'adaptation' => 0.0, 'total' => 0.0, 'projet' => 0.0, 'totalAvecProjet' => 0.0, 'tp_ressources' => 0.0, 'tp_adaptation' => 0.0, 'tp_total' => 0.0],
        ];

        $butTotal = [
            'ressources' => 0.0,
            'sae' => 0.0,
            'adaptation' => 0.0,
            'total' => 0.0,
            'projet' => 0.0,
            'totalAvecProjet' => 0.0,
            'tp_ressources' => 0.0,
            'tp_adaptation' => 0.0,
            'tp_total' => 0.0,
        ];

        foreach ($semestres as $semestre) {
            $ordre = $semestre->getOrdreLmd();
            if (isset($donnees[$ordre])) {
                $item = [
                    'semestre' => $semestre,
                    'ressources' => $donnees[$ordre]['totalEnseignementRessources'] ?? 0.0,
                    'sae' => $donnees[$ordre]['vhNbHeuresEnseignementSae'] ?? 0.0,
                    'adaptation' => $donnees[$ordre]['vhNbHeureeEnseignementSaeRessource'] ?? 0.0,
                    'total' => $donnees[$ordre]['totalEnseignements'] ?? 0.0,
                    'projet' => $donnees[$ordre]['totalProjetTutore'] ?? 0.0,
                    'totalAvecProjet' => $donnees[$ordre]['totalEnseignementProjetTutore'] ?? 0.0,
                    'tp_ressources' => $donnees[$ordre]['totalDontTpRessources'] ?? 0.0,
                    'tp_adaptation' => $donnees[$ordre]['vhNbHeuresDontTpSaeRessource'] ?? 0.0,
                    'tp_total' => $donnees[$ordre]['totalDontTp'] ?? 0.0,
                ];
                $semestresData[$ordre] = $item;

                $yearNum = (int)ceil($ordre / 2);
                if (isset($yearsData[$yearNum])) {
                    $yearsData[$yearNum]['ressources'] += $item['ressources'];
                    $yearsData[$yearNum]['sae'] += $item['sae'];
                    $yearsData[$yearNum]['adaptation'] += $item['adaptation'];
                    $yearsData[$yearNum]['total'] += $item['total'];
                    $yearsData[$yearNum]['projet'] += $item['projet'];
                    $yearsData[$yearNum]['totalAvecProjet'] += $item['totalAvecProjet'];
                    $yearsData[$yearNum]['tp_ressources'] += $item['tp_ressources'];
                    $yearsData[$yearNum]['tp_adaptation'] += $item['tp_adaptation'];
                    $yearsData[$yearNum]['tp_total'] += $item['tp_total'];
                }

                $butTotal['ressources'] += $item['ressources'];
                $butTotal['sae'] += $item['sae'];
                $butTotal['adaptation'] += $item['adaptation'];
                $butTotal['total'] += $item['total'];
                $butTotal['projet'] += $item['projet'];
                $butTotal['totalAvecProjet'] += $item['totalAvecProjet'];
                $butTotal['tp_ressources'] += $item['tp_ressources'];
                $butTotal['tp_adaptation'] += $item['tp_adaptation'];
                $butTotal['tp_total'] += $item['tp_total'];
            }
        }

        return $this->render('gt/synthese_heures.html.twig', [
            'currentDepartement' => $currentDepartement,
            'version' => $version,
            'parcours' => $parcours,
            'allParcours' => $allParcours,
            'semestresData' => $semestresData,
            'yearsData' => $yearsData,
            'butTotal' => $butTotal,
        ]);
    }
}
