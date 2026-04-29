<?php

namespace App\Pdf\Builder;

use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Latex\LatexSanitizer;
use App\Pdf\PdfPayloadBuilderInterface;
use App\Pdf\PdfSourceType;
use App\Pdf\RemotePdfRequest;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceRepository;
use Twig\Environment;
use ZipArchive;

final class RessourcePdfPayloadBuilder implements PdfPayloadBuilderInterface
{
    public function __construct(
        private readonly ApcRessourceRepository $ressourceRepository,
        private readonly ApcParcoursRepository $parcoursRepository,
        private readonly Environment $twig,
        private readonly LatexSanitizer $latexSanitizer,
    ) {
    }

    public function supports(PdfSourceType $sourceType, string $documentKey): bool
    {
        return $sourceType === PdfSourceType::RESSOURCE && $documentKey === 'fiche';
    }

    public function build(string $sourceId, string $documentKey, array $parameters = []): RemotePdfRequest
    {
        $ressource = $this->ressourceRepository->find($sourceId);

        if (!$ressource instanceof ApcRessource) {
            throw new \RuntimeException('Ressource introuvable.');
        }

        $parcours = $this->resolveParcours($parameters, $ressource);

        $workDir = sys_get_temp_dir() . '/pdf_' . uniqid('', true);
        if (!is_dir($workDir) && !mkdir($workDir, 0777, true) && !is_dir($workDir)) {
            throw new \RuntimeException('Impossible de créer le dossier temporaire.');
        }

        $entrypoint = 'main.tex';
        $mainTexPath = $workDir . '/' . $entrypoint;
        $zipPath = $workDir . '/archive.zip';

        try {
            $latex = $this->twig->render('latex/exemple_ressource.tex.twig', [
                'ressource' => $ressource,
                'semestre' => $ressource->getSemestre(),
                'parcours' => $parcours,
                'parameters' => $parameters,
            ]);
            $latex = $this->latexSanitizer->normalizeLatexDocument($latex);

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

            $stableLatex = $this->buildStableLatexFingerprint($latex);

            return new RemotePdfRequest(
                type: 'latex',
                options: [
                    'filename' => sprintf('ressource_%s.pdf', $ressource->getId()),
                    'timeoutSeconds' => 180,
                    'entrypoint' => $entrypoint,
                    'engine' => 'pdflatex',
                ],
                payload: [
                    'zipBase64' => $zipBase64,
                    'entrypoint' => $entrypoint,
                    'engine' => 'pdflatex',
                ],
                filename: sprintf('ressource_%s.pdf', $ressource->getId()),
                sourceHash: hash('sha256', json_encode([
                    'type' => 'latex',
                    'latex' => $stableLatex,
                    'parameters' => $parameters,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            );
        } finally {
            @unlink($mainTexPath);
            @unlink($zipPath);
            @rmdir($workDir);
        }





//        $html = $this->twig->render('pdf/ressource/fiche.html.twig', [
//            'ressource' => $ressource,
//            'parameters' => $parameters,
//        ]);
//
//        return new RemotePdfRequest(
//            type: 'html',
//            options: [
//                'filename' => sprintf('ressource_%s.pdf', $ressource->getId()),
//                'timeoutSeconds' => 120,
//                'pageFormat' => 'A4',
//                'marginTop' => '10mm',
//                'marginRight' => '10mm',
//                'marginBottom' => '10mm',
//                'marginLeft' => '10mm',
//            ],
//            payload: [
//                'html' => $html,
//            ],
//            filename: sprintf('ressource_%s.pdf', $ressource->getId()),
//            sourceHash: hash('sha256', json_encode([
//                'type' => 'html',
//                'html' => $html,
//                'parameters' => $parameters,
//            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
//        );
    }

    private function resolveParcours(array $parameters, ApcRessource $ressource): ?ApcParcours
    {
        $parcoursId = $parameters['parcours'] ?? null;
        if (!is_string($parcoursId) || $parcoursId === '') {
            return null;
        }

        $parcours = $this->parcoursRepository->find($parcoursId);
        if (!$parcours instanceof ApcParcours) {
            return null;
        }

        return $ressource->isGoodParcours($parcours) ? $parcours : null;
    }

    private function buildStableLatexFingerprint(string $latex): string
    {
        $withoutVolatileHeader = preg_replace('/^%%\s*Fichier généré le .*\R?/mu', '', $latex);

        return is_string($withoutVolatileHeader) ? $withoutVolatileHeader : $latex;
    }
}
