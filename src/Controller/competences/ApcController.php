<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller\competences;

use App\Classes\Apc\ApcStructure;
use App\Classes\Export\CompetencesExport;
use App\Classes\Export\DepartementExport;
use App\Classes\Import\MyUpload;
use App\Classes\Import\ReferentielCompetenceImport;
use App\Controller\BaseController;
use App\Entity\Constantes;
use App\Entity\Version;
use App\Repository\DepartementRepository;
use App\Classes\JsonDiffService;
use App\Utils\Files;
use Exception;
//use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
//use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/apc/referentiel-competences")]
class ApcController extends BaseController
{
    #[Route("/consulter/{version}", name:"administration_apc_referentiel_index", methods:["GET"])]
    public function referentiel(
        ApcStructure $apcStructure, Version $version = null): Response
    {
        if (null === $version) {
            throw new Exception('Departement inconnu');
        }


        $tParcours = $apcStructure->parcoursNiveaux($version);
        $competences = $version->getApcCompetences();
        $tComp = [];

        foreach ($competences as $comp) {
            $tComp[$comp->getId()] = $comp;
        }

        $competencesParcours = [];

        foreach ($tParcours as $key => $parc) {
            $competencesParcours[$key] = [];
            foreach ($parc as $k => $v) {
                $competencesParcours[$key][] = $tComp[$k];
            }
        }


        return $this->render('competences/referentiel.html.twig', [
            'competencesParcours' => $competencesParcours,
            'departement' => $version->getDepartement(),
            'version' => $version,
            'competences' => $competences,
            'parcours' => $version->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
        ]);
    }

    #[Route('/exporter/{version}', name: 'export_referentiel_competences', methods: ['GET'])]
    public function exportReferentiel(
//        Pdf $knpSnappyPdf,
        ApcStructure $apcStructure, Version $version = null)
    {
        throw new Exception('Fonctionnalité temporairement indisponible');
        if (null === $version) {
            throw new Exception('Departement inconnu');
        }

        $tParcours = $apcStructure->parcoursNiveaux($version);
        $competences = $version->getApcCompetences();
        $tComp = [];
        foreach ($competences as $comp) {
            $tComp[$comp->getId()] = $comp;
        }

        $competencesParcours = [];

        foreach ($tParcours as $key => $parc) {
            $competencesParcours[$key] = [];
            foreach ($parc as $k => $v) {
                $competencesParcours[$key][] = $tComp[$k];
            }
        }

        $html = $this->renderView('competences/export-referentiel.html.twig',[
            'competencesParcours' => $competencesParcours,
            'departement' => $version->getDepartement(),
            'competences' => $competences,
            'parcours' => $version->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
        ]);

//        return new PdfResponse(
//            $knpSnappyPdf->getOutputFromHtml($html, [
//                'orientation'=>'Landscape'
//            ]),
//            'referentiel-competence-'.$version->getDepartement()->getSigle().'.pdf'
//        );
    }

    #[Route('/exporter-versionning/{version}', name: 'export_versionning_referentiel_competences', methods: ['GET'])]
    public function exportVersionReferentiel(
        CompetencesExport $competencesExport,
        Version $version = null): Response //PdfResponse
    {
        return $competencesExport->generePdfVersionCompetences($version);
    }

    #[Route('/voir-versionning/{version}', name: 'voir_versionning_referentiel_competences', methods: ['GET'])]
    public function voirVersionReferentiel(
        Files $files,
        DepartementExport $departementExport,
        ApcStructure $apcStructure, Version $version = null): Response //PdfResponse
    {
        if (null === $version) {
            throw new Exception('Departement inconnu');
        }
//todo: A REFAIRE...
        // version précédente :
        $fichier = $files->getLastVersionFile($version->getDepartement());
        $tabAncien = json_decode(file_get_contents($fichier), true);
        $departement = $version->getDepartement();
        // version courante :
        $tabActuel = $departementExport->genereJson($version);

        $diffService = new JsonDiffService();
        $diffs = $diffService->compare($tabAncien, $tabActuel);
        $tParcours = $apcStructure->parcoursNiveaux($version);
        $competences = $version->getApcCompetences();
        $tComp = [];
        foreach ($competences as $comp) {
            $tComp[$comp->getId()] = $comp;
        }

        $competencesParcours = [];

        foreach ($tParcours as $key => $parc) {
            $competencesParcours[$key] = [];
            foreach ($parc as $k => $v) {
                $competencesParcours[$key][] = $tComp[$k];
            }
        }

        return  $this->render('competences/voir-versionning-referentiel.html.twig',[
            'competencesParcours' => $competencesParcours,
            'departement' => $departement,
            'competences' => $competences,
            'parcours' => $version->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
            'diffs' => $diffs,
        ]);
    }

        #[Route("/comparer", name:"administration_apc_referentiel_comparer", methods:["GET"])]
    public function comparerReferentiels(
        ApcStructure $apcStructure
    ): Response {
        $versionActuelle = $this->getVersion();
        $versionPrecedente = $this->getVersion()?->getPreviousVersion();



        if (null === $versionActuelle || null === $versionPrecedente) {
            throw new Exception('Les deux versions sont nécessaires');
        }

        $tParcoursActuel = $apcStructure->parcoursNiveaux($versionActuelle);
        $tParcoursPrecedent = $apcStructure->parcoursNiveaux($versionPrecedente);

        $parcoursActuelsById = [];
        foreach ($versionActuelle->getApcParcours() as $parcours) {
            if (null !== $parcours->getId()) {
                $parcoursActuelsById[$parcours->getId()] = $parcours;
            }
        }

        $parcoursPrecedentsById = [];
        foreach ($versionPrecedente->getApcParcours() as $parcours) {
            if (null !== $parcours->getId()) {
                $parcoursPrecedentsById[$parcours->getId()] = $parcours;
            }
        }

        $competencesActuellesById = [];
        foreach ($versionActuelle->getApcCompetences() as $competence) {
            if (null !== $competence->getId()) {
                $competencesActuellesById[$competence->getId()] = $competence;
            }
        }

        $competencesPrecedentesById = [];
        foreach ($versionPrecedente->getApcCompetences() as $competence) {
            if (null !== $competence->getId()) {
                $competencesPrecedentesById[$competence->getId()] = $competence;
            }
        }

        $parcoursDataActuel = [];
        foreach ($tParcoursActuel as $parcoursId => $competencesIds) {
            $parcours = $parcoursActuelsById[(int) $parcoursId] ?? null;
            if (null === $parcours || null === $parcours->getCode()) {
                continue;
            }

            $code = $parcours->getCode();
            $parcoursDataActuel[$code] = [
                'code' => $code,
                'libelle' => $parcours->getLibelle() ?? $code,
                'competences' => [],
            ];

            foreach ($competencesIds as $competenceId => $unused) {
                $competence = $competencesActuellesById[(int) $competenceId] ?? null;
                if (null !== $competence) {
                    $parcoursDataActuel[$code]['competences'][] = $competence;
                }
            }
        }

        $parcoursDataPrecedent = [];
        foreach ($tParcoursPrecedent as $parcoursId => $competencesIds) {
            $parcours = $parcoursPrecedentsById[(int) $parcoursId] ?? null;
            if (null === $parcours || null === $parcours->getCode()) {
                continue;
            }

            $code = $parcours->getCode();
            $parcoursDataPrecedent[$code] = [
                'code' => $code,
                'libelle' => $parcours->getLibelle() ?? $code,
                'competences' => [],
            ];

            foreach ($competencesIds as $competenceId => $unused) {
                $competence = $competencesPrecedentesById[(int) $competenceId] ?? null;
                if (null !== $competence) {
                    $parcoursDataPrecedent[$code]['competences'][] = $competence;
                }
            }
        }

        $allCodes = array_unique(array_merge(array_keys($parcoursDataActuel), array_keys($parcoursDataPrecedent)));
        sort($allCodes);

        $comparaisonParcours = [];
        foreach ($allCodes as $code) {
            $actuel = $parcoursDataActuel[$code] ?? ['code' => $code, 'libelle' => $code, 'competences' => []];
            $precedent = $parcoursDataPrecedent[$code] ?? ['code' => $code, 'libelle' => $code, 'competences' => []];

            $actuellesByNomCourt = [];
            foreach ($actuel['competences'] as $competence) {
                $cle = $this->normalizeComparisonKey($competence->getNomCourt());
                if ('' !== $cle) {
                    $actuellesByNomCourt[$cle] = $competence;
                }
            }

            $precedentesByNomCourt = [];
            foreach ($precedent['competences'] as $competence) {
                $cle = $this->normalizeComparisonKey($competence->getNomCourt());
                if ('' !== $cle) {
                    $precedentesByNomCourt[$cle] = $competence;
                }
            }

            $allCompetenceKeys = array_unique(array_merge(array_keys($actuellesByNomCourt), array_keys($precedentesByNomCourt)));
            sort($allCompetenceKeys);

            $competenceComparaisons = [];
            $ajouteesCount = 0;
            $supprimeesCount = 0;
            $modifieesCount = 0;

            foreach ($allCompetenceKeys as $competenceKey) {
                $competenceActuelle = $actuellesByNomCourt[$competenceKey] ?? null;
                $competencePrecedente = $precedentesByNomCourt[$competenceKey] ?? null;

                $diff = $this->buildCompetenceDiff($competencePrecedente, $competenceActuelle);

                if ($diff['isAdded']) {
                    ++$ajouteesCount;
                } elseif ($diff['isRemoved']) {
                    ++$supprimeesCount;
                } elseif ($diff['hasChanges']) {
                    ++$modifieesCount;
                }

                $competenceComparaisons[] = [
                    'nomCourt' => $competenceActuelle?->getNomCourt() ?? $competencePrecedente?->getNomCourt() ?? strtoupper($competenceKey),
                    'actuelle' => $competenceActuelle,
                    'precedente' => $competencePrecedente,
                    'isAdded' => $diff['isAdded'],
                    'isRemoved' => $diff['isRemoved'],
                    'hasChanges' => $diff['hasChanges'],
                    'sections' => $diff['sections'],
                    'changesCount' => $diff['changesCount'],
                ];
            }

            $comparaisonParcours[$code] = [
                'code' => $code,
                'libelle' => $actuel['libelle'] ?? $precedent['libelle'] ?? $code,
                'competencesActuelles' => array_values($actuellesByNomCourt),
                'competencesPrecedentes' => array_values($precedentesByNomCourt),
                'ajouteesCount' => $ajouteesCount,
                'supprimeesCount' => $supprimeesCount,
                'modifieesCount' => $modifieesCount,
                'competenceComparaisons' => $competenceComparaisons,
            ];
        }

        return $this->render('competences/comparer-referentiel.html.twig', [
            'comparaisonParcours' => $comparaisonParcours,
            'versionActuelle' => $versionActuelle,
            'versionPrecedente' => $versionPrecedente,
            'departement' => $versionActuelle->getDepartement(),
        ]);
    }

    private function buildCompetenceDiff($precedente, $actuelle): array
    {
        $sections = [
            'libelle' => [],
            'composantes' => [],
            'situations' => [],
            'niveaux' => [],
            'acs' => [],
        ];

        if (null === $precedente && null !== $actuelle) {
            $sections['libelle'][] = ['type' => 'add', 'text' => '+ Competence ajoutee'];

            return [
                'isAdded' => true,
                'isRemoved' => false,
                'hasChanges' => true,
                'changesCount' => 1,
                'sections' => $sections,
            ];
        }

        if (null !== $precedente && null === $actuelle) {
            $sections['libelle'][] = ['type' => 'remove', 'text' => '- Competence supprimee'];

            return [
                'isAdded' => false,
                'isRemoved' => true,
                'hasChanges' => true,
                'changesCount' => 1,
                'sections' => $sections,
            ];
        }

        if (null === $precedente || null === $actuelle) {
            return [
                'isAdded' => false,
                'isRemoved' => false,
                'hasChanges' => false,
                'changesCount' => 0,
                'sections' => $sections,
            ];
        }

        $snapshotPrecedent = $this->buildCompetenceSnapshot($precedente);
        $snapshotActuel = $this->buildCompetenceSnapshot($actuelle);

        if ($snapshotPrecedent['libelle'] !== $snapshotActuel['libelle']) {
            $sections['libelle'][] = [
                'type' => 'change',
                'text' => '~ Libelle',
                'old' => $snapshotPrecedent['libelle'],
                'new' => $snapshotActuel['libelle'],
            ];
        }

        $sections['composantes'] = $this->buildAssocDiffLines(
            $snapshotPrecedent['composantes'],
            $snapshotActuel['composantes'],
            'Composante'
        );

        $sections['situations'] = $this->buildAssocDiffLines(
            $snapshotPrecedent['situations'],
            $snapshotActuel['situations'],
            'Situation'
        );

        $allNiveaux = array_unique(array_merge(array_keys($snapshotPrecedent['niveaux']), array_keys($snapshotActuel['niveaux'])));
        sort($allNiveaux);

        foreach ($allNiveaux as $ordre) {
            $oldNiveau = $snapshotPrecedent['niveaux'][$ordre] ?? null;
            $newNiveau = $snapshotActuel['niveaux'][$ordre] ?? null;

            if (null === $oldNiveau && null !== $newNiveau) {
                $sections['niveaux'][] = ['type' => 'add', 'text' => '+ Niveau '.$ordre.' ajoute: '.$newNiveau['libelle']];
                foreach ($newNiveau['acs'] as $acKey => $acLabel) {
                    $sections['acs'][] = ['type' => 'add', 'text' => '+ Niveau '.$ordre.' / AC '.$acKey.' : '.$acLabel];
                }
                continue;
            }

            if (null !== $oldNiveau && null === $newNiveau) {
                $sections['niveaux'][] = ['type' => 'remove', 'text' => '- Niveau '.$ordre.' supprime: '.$oldNiveau['libelle']];
                foreach ($oldNiveau['acs'] as $acKey => $acLabel) {
                    $sections['acs'][] = ['type' => 'remove', 'text' => '- Niveau '.$ordre.' / AC '.$acKey.' : '.$acLabel];
                }
                continue;
            }

            if (null !== $oldNiveau && null !== $newNiveau) {
                if ($oldNiveau['libelle'] !== $newNiveau['libelle']) {
                    $sections['niveaux'][] = [
                        'type' => 'change',
                        'text' => '~ Niveau '.$ordre,
                        'old' => $oldNiveau['libelle'],
                        'new' => $newNiveau['libelle'],
                    ];
                }

                $acDiffs = $this->buildAssocDiffLines($oldNiveau['acs'], $newNiveau['acs'], 'Niveau '.$ordre.' / AC');
                $sections['acs'] = array_merge($sections['acs'], $acDiffs);
            }
        }

        $changesCount = count($sections['libelle']) + count($sections['composantes']) + count($sections['situations']) + count($sections['niveaux']) + count($sections['acs']);

        return [
            'isAdded' => false,
            'isRemoved' => false,
            'hasChanges' => $changesCount > 0,
            'changesCount' => $changesCount,
            'sections' => $sections,
        ];
    }

    private function buildCompetenceSnapshot($competence): array
    {
        $composantes = [];
        foreach ($competence->getApcComposanteEssentielles() as $composante) {
            $key = $this->normalizeComparisonKey((string) ($composante->getCode() ?? $composante->getOrdre()));
            if ('' !== $key) {
                $composantes[$key] = trim(((string) $composante->getCode()).' | '.((string) $composante->getLibelle()));
            }
        }
        ksort($composantes);

        $situations = [];
        foreach ($competence->getApcSituationProfessionnelles() as $situation) {
            $key = $this->normalizeComparisonKey($situation->getLibelle());
            if ('' !== $key) {
                $situations[$key] = (string) $situation->getLibelle();
            }
        }
        ksort($situations);

        $niveaux = [];
        foreach ($competence->getApcNiveaux() as $niveau) {
            $ordre = (int) $niveau->getOrdre();
            if (0 === $ordre) {
                continue;
            }

            $acs = [];
            foreach ($niveau->getApcApprentissageCritiques() as $ac) {
                $acKey = $this->normalizeComparisonKey((string) ($ac->getCode() ?? $ac->getOrdre()));
                if ('' !== $acKey) {
                    $acs[$acKey] = trim(((string) $ac->getCode()).' | '.((string) $ac->getLibelle()));
                }
            }
            ksort($acs);

            $niveaux[$ordre] = [
                'libelle' => (string) $niveau->getLibelle(),
                'acs' => $acs,
            ];
        }
        ksort($niveaux);

        return [
            'libelle' => (string) $competence->getLibelle(),
            'composantes' => $composantes,
            'situations' => $situations,
            'niveaux' => $niveaux,
        ];
    }

    private function buildAssocDiffLines(array $oldValues, array $newValues, string $label): array
    {
        $lines = [];
        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
        sort($allKeys);

        foreach ($allKeys as $key) {
            $inOld = array_key_exists($key, $oldValues);
            $inNew = array_key_exists($key, $newValues);

            if (!$inOld && $inNew) {
                $lines[] = ['type' => 'add', 'text' => '+ '.$label.' '.$key.' : '.$newValues[$key]];
                continue;
            }

            if ($inOld && !$inNew) {
                $lines[] = ['type' => 'remove', 'text' => '- '.$label.' '.$key.' : '.$oldValues[$key]];
                continue;
            }

            if ($inOld && $inNew && $oldValues[$key] !== $newValues[$key]) {
                $lines[] = [
                    'type' => 'change',
                    'text' => '~ '.$label.' '.$key,
                    'old' => $oldValues[$key],
                    'new' => $newValues[$key],
                ];
            }
        }

        return $lines;
    }

    private function normalizeComparisonKey(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    #[Route("/import", name:"administration_apc_referentiel_import", methods:["GET","POST"])]
    public function import(
        DepartementRepository $departementRepository,
        MyUpload $myUpload,
        ReferentielCompetenceImport $diplomeImport,
        Request $request
    ): Response {
        if ($request->isMethod('POST') && null !== $this->getDepartement()) {
            $fichier = $myUpload->upload($request->files->get('fichier'), 'temp/', ['xml', 'xlsx']);
            $diplomeImport->import($this->getVersion(), $fichier, $request->request->get('typeFichier'));
            unlink($fichier);
            $this->addFlashBag(Constantes::FLASHBAG_SUCCESS, 'Maquette importée avec succès');
        }

        return $this->render('import_referentiel/index.html.twig', [
            'departements' => $departementRepository->findAll(),
        ]);
    }
}
