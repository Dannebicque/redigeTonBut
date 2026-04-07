<?php

namespace App\Pdf\Builder;

use App\Classes\Apc\ApcStructure;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Version;
use App\Pdf\PdfPayloadBuilderInterface;
use App\Pdf\PdfSourceType;
use App\Pdf\RemotePdfRequest;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\SemestreRepository;
use App\Repository\VersionRepository;
use Twig\Environment;
use ZipArchive;

final class ReferentielPdfPayloadBuilder implements PdfPayloadBuilderInterface
{
    public function __construct(
        private readonly VersionRepository $versionRepository,
        private readonly ApcParcoursRepository $apcParcoursRepository,
        private readonly SemestreRepository $semestreRepository,
        private readonly ApcRessourceRepository $apcRessourceRepository,
        private readonly ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        private readonly ApcSaeRepository $apcSaeRepository,
        private readonly ApcSaeParcoursRepository $apcSaeParcoursRepository,
        private readonly ApcStructure $apcStructure,
        private readonly Environment $twig,
    ) {
    }

    public function supports(PdfSourceType $sourceType, string $documentKey): bool
    {
        return $sourceType === PdfSourceType::REFERENTIEL && str_starts_with($documentKey, 'export_latex');
    }

    public function build(string $sourceId, string $documentKey, array $parameters = []): RemotePdfRequest
    {
        $version = $this->versionRepository->find($sourceId);

        if (!$version instanceof Version) {
            throw new \RuntimeException('Version de référentiel introuvable.');
        }

        $mode = $this->resolveMode($documentKey);
        $parcours = $this->resolveParcours($documentKey, $version);
        $data = $this->buildLatexData($version, $mode, $parcours);

        $workDir = sys_get_temp_dir() . '/pdf_' . uniqid('', true);
        if (!is_dir($workDir) && !mkdir($workDir, 0777, true) && !is_dir($workDir)) {
            throw new \RuntimeException('Impossible de créer le dossier temporaire.');
        }

        $entrypoint = 'main.tex';
        $mainTexPath = $workDir . '/' . $entrypoint;
        $zipPath = $workDir . '/archive.zip';

        try {
            $latex = $this->twig->render('latex/remote_referentiel_export.tex.twig', $data);
            file_put_contents($mainTexPath, $latex);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Impossible de créer l’archive ZIP LaTeX.');
            }

            $zip->addFile($mainTexPath, $entrypoint);
            $zip->close();

            $zipContent = file_get_contents($zipPath);
            if ($zipContent === false) {
                throw new \RuntimeException('Impossible de lire l’archive ZIP LaTeX.');
            }

            $zipBase64 = base64_encode($zipContent);

            return new RemotePdfRequest(
                type: 'latex',
                options: [
                    'filename' => sprintf('referentiel_%s_%s.pdf', $sourceId, $documentKey),
                    'timeoutSeconds' => 180,
                    'entrypoint' => $entrypoint,
                    'engine' => 'pdflatex',
                ],
                payload: [
                    'zipBase64' => $zipBase64,
                    'entrypoint' => $entrypoint,
                    'engine' => 'pdflatex',
                ],
                filename: sprintf('referentiel_%s_%s.pdf', $sourceId, $documentKey),
                sourceHash: hash('sha256', json_encode([
                    'type' => 'latex',
                    'documentKey' => $documentKey,
                    'latex' => $latex,
                    'parameters' => $parameters,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            );
        } finally {
            @unlink($mainTexPath);
            @unlink($zipPath);
            @rmdir($workDir);
        }
    }

    private function resolveMode(string $documentKey): string
    {
        if ($documentKey === 'export_latex_tronc_commun') {
            return 'tronc_commun';
        }

        if ($documentKey === 'export_latex_parcours') {
            return 'parcours';
        }

        if (str_starts_with($documentKey, 'export_latex_al_')) {
            return 'adaptation_locale';
        }

        if (str_starts_with($documentKey, 'export_latex_parcours_')) {
            return 'parcours_detail';
        }

        return 'parcours';
    }

    private function resolveParcours(string $documentKey, Version $version): ?ApcParcours
    {
        if (preg_match('/^export_latex_(?:parcours|al)_(\d+)$/', $documentKey, $matches) !== 1) {
            return null;
        }

        $parcours = $this->apcParcoursRepository->find((int) $matches[1]);
        if (!$parcours instanceof ApcParcours) {
            throw new \RuntimeException('Parcours introuvable pour l\'export.');
        }

        if ($parcours->getVersion()?->getId() !== $version->getId()) {
            throw new \RuntimeException('Le parcours ne correspond pas à la version demandée.');
        }

        return $parcours;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLatexData(Version $version, string $mode, ?ApcParcours $parcours): array
    {
        $departement = $version->getDepartement();
        if (!$departement instanceof Departement) {
            throw new \RuntimeException('Département introuvable pour la version.');
        }

        $competencesParcours = $this->buildCompetencesParcours($version);
        $semestres = [];
        $ressources = [];
        $saes = [];

        if ($mode === 'tronc_commun' || $mode === 'parcours') {
            $nbParcours = $version->getApcParcours()->count();

            foreach ($version->getSemestres() as $semestre) {
                $semestres[] = $semestre;
                $ressources[$semestre->getId()] = [];
                $allRessources = $this->apcRessourceRepository->findBySemestre($semestre);

                if ($mode === 'tronc_commun' && $semestre->getOrdreLmd() < 3) {
                    $ressources[$semestre->getId()] = $allRessources;
                    continue;
                }

                foreach ($allRessources as $ressource) {
                    $nbAffectations = $ressource->getApcRessourceParcours()->count();

                    if ($mode === 'tronc_commun' && ($nbAffectations === $nbParcours || $nbAffectations === 0)) {
                        $ressources[$semestre->getId()][] = $ressource;
                    }

                    if ($mode === 'parcours' && $semestre->getOrdreLmd() > 2 && $nbAffectations > 0 && $nbAffectations < $nbParcours) {
                        $ressources[$semestre->getId()][] = $ressource;
                    }
                }
            }
        }

        if ($mode === 'parcours_detail' || $mode === 'adaptation_locale') {
            if (!$parcours instanceof ApcParcours) {
                throw new \RuntimeException('Parcours obligatoire pour cet export.');
            }

            $isAdaptationLocale = $mode === 'adaptation_locale';
            $semestres = $departement->getTypeStructure() === Departement::TYPE3
                ? $this->semestreRepository->findByParcours($parcours)
                : $version->getSemestres();

            foreach ($semestres as $semestre) {
                if ($departement->getTypeStructure() !== Departement::TYPE3 && $semestre->getOrdreLmd() < 3) {
                    $ressources[$semestre->getId()] = $isAdaptationLocale
                        ? $this->apcRessourceRepository->findBySemestreAl($semestre)
                        : $this->apcRessourceRepository->findBySemestre($semestre);

                    $saes[$semestre->getId()] = $isAdaptationLocale
                        ? $this->apcSaeRepository->findBySemestreAl($semestre)
                        : $this->apcSaeRepository->findBySemestre($semestre);

                    continue;
                }

                $ressources[$semestre->getId()] = $isAdaptationLocale
                    ? $this->apcRessourceParcoursRepository->findBySemestreAl($semestre, $parcours)
                    : $this->apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);

                $saes[$semestre->getId()] = $isAdaptationLocale
                    ? $this->apcSaeParcoursRepository->findBySemestreAl($semestre, $parcours)
                    : $this->apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
            }
        }

        return [
            'mode' => $mode,
            'version' => $version,
            'departement' => $departement,
            'parcours' => $parcours,
            'allParcours' => $version->getApcParcours(),
            'semestres' => $semestres,
            'ressources' => $ressources,
            'saes' => $saes,
            'competencesParcours' => $competencesParcours,
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function buildCompetencesParcours(Version $version): array
    {
        $competences = [];
        foreach ($version->getApcCompetences() as $competence) {
            $competences[$competence->getId()] = $competence;
        }

        $parcoursNiveaux = $this->apcStructure->parcoursNiveaux($version);
        $result = [];

        foreach ($parcoursNiveaux as $parcoursId => $niveauxByCompetence) {
            $result[$parcoursId] = [];
            foreach ($niveauxByCompetence as $competenceId => $niveaux) {
                if (isset($competences[$competenceId])) {
                    $result[$parcoursId][] = $competences[$competenceId];
                }
            }
        }

        return $result;
    }
}
