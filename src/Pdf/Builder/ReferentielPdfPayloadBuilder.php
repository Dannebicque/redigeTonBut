<?php

namespace App\Pdf\Builder;

use App\Classes\Latex\GenereFile;
use App\Entity\Version;
use App\Pdf\PdfPayloadBuilderInterface;
use App\Pdf\PdfSourceType;
use App\Pdf\RemotePdfRequest;
use App\Repository\VersionRepository;
use ZipArchive;

final class ReferentielPdfPayloadBuilder implements PdfPayloadBuilderInterface
{
    public const DOCUMENT_KEY_COMPLETE = 'export_latex_complet';

    public function __construct(
        private readonly VersionRepository $versionRepository,
        private readonly GenereFile $genereFile,
        private readonly string $projectDir,
    )
    {
    }

    public function supports(PdfSourceType $sourceType, string $documentKey): bool
    {
        return $sourceType === PdfSourceType::REFERENTIEL
            && $documentKey === self::DOCUMENT_KEY_COMPLETE;
    }

    public function build(string $sourceId, string $documentKey, array $parameters = []): RemotePdfRequest
    {
        $version = $this->versionRepository->find($sourceId);

        if (!$version instanceof Version) {
            throw new \RuntimeException('Version de référentiel introuvable.');
        }

        $includeAssets = ($parameters['includeAssets'] ?? true) !== false;

        $workDir = sys_get_temp_dir() . '/pdf_' . uniqid('', true);
        if (!is_dir($workDir) && !mkdir($workDir, 0777, true) && !is_dir($workDir)) {
            throw new \RuntimeException('Impossible de créer le dossier temporaire.');
        }

        $entrypoint = 'main.tex';
        $mainTexPath = $workDir . '/' . $entrypoint;
        $zipPath = $workDir . '/archive.zip';

        try {
            $latex = $this->genereFile->renderContent($version, $includeAssets);
            file_put_contents($mainTexPath, $latex);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Impossible de créer l’archive ZIP LaTeX.');
            }

            $zip->addFile($mainTexPath, $entrypoint);

            $archiveAssets = $this->genereFile->getArchiveAssets($version, $includeAssets);
            foreach ($archiveAssets as $archivePath => $sourcePath) {
                if ($zip->addFile($sourcePath, $archivePath) !== true) {
                    throw new \RuntimeException(sprintf('Impossible d’ajouter l’asset LaTeX %s à l’archive.', $archivePath));
                }
            }

            $zip->close();

            $zipContent = file_get_contents($zipPath);
            if ($zipContent === false) {
                throw new \RuntimeException('Impossible de lire l’archive ZIP LaTeX.');
            }

            $this->persistDebugArtifacts($sourceId, $documentKey, $latex, $zipContent, $parameters, $entrypoint, array_keys($archiveAssets));

            $zipBase64 = base64_encode($zipContent);

            $stableLatex = $this->buildStableLatexFingerprint($latex);

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
                    'latex' => $stableLatex,
                    'includeAssets' => $includeAssets,
                    'assets' => $this->buildAssetFingerprints($archiveAssets),
                    'parameters' => $parameters,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            );
        } finally {
            @unlink($mainTexPath);
            @unlink($zipPath);
            @rmdir($workDir);
        }
    }

    /**
     * Sauvegarde une copie exacte du contenu envoyé au service distant pour diagnostic.
     */
    private function persistDebugArtifacts(
        string $sourceId,
        string $documentKey,
        string $latex,
        string $zipContent,
        array  $parameters,
        string $entrypoint,
        array $archiveEntries,
    ): void
    {
        try {
            $rootDir = $this->projectDir . '/var/pdf-debug/referentiel';
            if (!is_dir($rootDir) && !mkdir($rootDir, 0777, true) && !is_dir($rootDir)) {
                return;
            }

            $safeSourceId = preg_replace('/[^A-Za-z0-9_-]/', '_', $sourceId) ?? 'source';
            $safeDocumentKey = preg_replace('/[^A-Za-z0-9_-]/', '_', $documentKey) ?? 'document';
            $dumpDirName = sprintf('%s_%s_%s_%s', date('Ymd_His'), $safeSourceId, $safeDocumentKey, substr(bin2hex(random_bytes(6)), 0, 12));
            $dumpDir = $rootDir . '/' . $dumpDirName;

            if (!mkdir($dumpDir, 0777, true) && !is_dir($dumpDir)) {
                return;
            }

            file_put_contents($dumpDir . '/main.tex', $latex);
            file_put_contents($dumpDir . '/archive.zip', $zipContent);

            $meta = [
                'createdAt' => date('c'),
                'sourceId' => $sourceId,
                'documentKey' => $documentKey,
                'entrypoint' => $entrypoint,
                'engine' => 'pdflatex',
                'latexBytes' => strlen($latex),
                'zipBytes' => strlen($zipContent),
                'latexSha256' => hash('sha256', $latex),
                'zipSha256' => hash('sha256', $zipContent),
                'parameters' => $parameters,
                'archiveEntries' => $archiveEntries,
            ];

            $metaJson = json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (is_string($metaJson)) {
                file_put_contents($dumpDir . '/meta.json', $metaJson);
            }
        } catch (\Throwable) {
            // Le diagnostic ne doit jamais bloquer la génération.
        }
    }

    /**
     * @param array<string, string> $archiveAssets
     *
     * @return array<string, string>
     */
    private function buildAssetFingerprints(array $archiveAssets): array
    {
        $fingerprints = [];

        foreach ($archiveAssets as $archivePath => $sourcePath) {
            $hash = hash_file('sha256', $sourcePath);
            if ($hash !== false) {
                $fingerprints[$archivePath] = $hash;
            }
        }

        ksort($fingerprints);

        return $fingerprints;
    }

    private function buildStableLatexFingerprint(string $latex): string
    {
        $withoutVolatileHeader = preg_replace('/^%%\s*Fichier généré le .*\R?/mu', '', $latex);

        return is_string($withoutVolatileHeader) ? $withoutVolatileHeader : $latex;
    }
}
