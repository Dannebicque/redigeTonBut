<?php

namespace App\Pdf\Builder;

use App\Pdf\PdfPayloadBuilderInterface;
use App\Pdf\PdfSourceType;
use App\Pdf\RemotePdfRequest;
use ZipArchive;

final class ReferentielPdfPayloadBuilder implements PdfPayloadBuilderInterface
{
    public function __construct(
        // remplace par ton vrai repository / service métier
        private readonly \App\Repository\ApcComptenceRepository $repository,
    ) {
    }

    public function supports(PdfSourceType $sourceType, string $documentKey): bool
    {
        return $sourceType === PdfSourceType::REFERENTIEL && $documentKey === 'export_latex';
    }

    public function build(string $sourceId, string $documentKey, array $parameters = []): RemotePdfRequest
    {
        $referentiel = $this->repository->find($sourceId);

        if (!$referentiel) {
            throw new \RuntimeException('Référentiel introuvable.');
        }

        $workDir = sys_get_temp_dir() . '/pdf_' . uniqid('', true);
        if (!is_dir($workDir) && !mkdir($workDir, 0777, true) && !is_dir($workDir)) {
            throw new \RuntimeException('Impossible de créer le dossier temporaire.');
        }

        $entrypoint = 'main.tex';
        $mainTexPath = $workDir . '/' . $entrypoint;
        $zipPath = $workDir . '/archive.zip';

        try {
            $latex = $this->buildLatex($referentiel, $parameters);
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
                    'filename' => sprintf('referentiel_%s.pdf', $sourceId),
                    'timeoutSeconds' => 180,
                    'entrypoint' => $entrypoint,
                    'engine' => 'pdflatex',
                ],
                payload: [
                    'zipBase64' => $zipBase64,
                    'entrypoint' => $entrypoint,
                    'engine' => 'pdflatex',
                ],
                filename: sprintf('referentiel_%s.pdf', $sourceId),
                sourceHash: hash('sha256', json_encode([
                    'type' => 'latex',
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

    private function buildLatex(object $referentiel, array $parameters = []): string
    {
        $label = method_exists($referentiel, 'getLibelle') ? (string) $referentiel->getLibelle() : 'Référentiel';

        return <<<LATEX
\\documentclass{article}
\\usepackage[utf8]{inputenc}
\\usepackage[T1]{fontenc}
\\usepackage[french]{babel}

\\begin{document}

\\section*{Référentiel de compétences}

{$this->escapeLatex($label)}

\\end{document}
LATEX;
    }

    private function escapeLatex(string $value): string
    {
        return str_replace(
            ['\\', '&', '%', '$', '#', '_', '{', '}', '~', '^'],
            ['\\textbackslash{}', '\\&', '\\%', '\\$', '\\#', '\\_', '\\{', '\\}', '\\textasciitilde{}', '\\textasciicircum{}'],
            $value
        );
    }
}
