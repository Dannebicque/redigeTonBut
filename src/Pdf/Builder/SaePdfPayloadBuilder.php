<?php

namespace App\Pdf\Builder;

use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Pdf\PdfPayloadBuilderInterface;
use App\Pdf\PdfSourceType;
use App\Pdf\RemotePdfRequest;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcSaeRepository;
use Twig\Environment;
use ZipArchive;

final readonly class SaePdfPayloadBuilder implements PdfPayloadBuilderInterface
{
    public function __construct(
        private ApcSaeRepository $saeRepository,
        private ApcParcoursRepository $parcoursRepository,
        private Environment      $twig,
    ) {
    }

    public function supports(PdfSourceType $sourceType, string $documentKey): bool
    {
        return $sourceType === PdfSourceType::SAE && $documentKey === 'fiche';
    }

    public function build(string $sourceId, string $documentKey, array $parameters = []): RemotePdfRequest
    {
        $sae = $this->saeRepository->find($sourceId);

        if (!$sae instanceof ApcSae) {
            throw new \RuntimeException('SAE introuvable.');
        }

        $parcours = $this->resolveParcours($parameters, $sae);

        $workDir = sys_get_temp_dir() . '/pdf_' . uniqid('', true);
        if (!is_dir($workDir) && !mkdir($workDir, 0777, true) && !is_dir($workDir)) {
            throw new \RuntimeException('Impossible de créer le dossier temporaire.');
        }

        $entrypoint = 'main.tex';
        $mainTexPath = $workDir . '/' . $entrypoint;
        $zipPath = $workDir . '/archive.zip';

        try {
            $latex = $this->twig->render('latex/exemple_sae.tex.twig', [
                'sae' => $sae,
                'semestre' => $sae->getSemestre(),
                'parcours' => $parcours,
                'parameters' => $parameters,
            ]);

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
                    'filename' => sprintf('sae_%s.pdf', $sae->getId()),
                    'timeoutSeconds' => 180,
                    'entrypoint' => $entrypoint,
                    'engine' => 'pdflatex',
                ],
                payload: [
                    'zipBase64' => $zipBase64,
                    'entrypoint' => $entrypoint,
                    'engine' => 'pdflatex',
                ],
                filename: sprintf('sae_%s.pdf', $sae->getId()),
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

    private function resolveParcours(array $parameters, ApcSae $sae): ?ApcParcours
    {
        $parcoursId = $parameters['parcours'] ?? null;
        if (!is_string($parcoursId) || $parcoursId === '') {
            return null;
        }

        $parcours = $this->parcoursRepository->find($parcoursId);
        if (!$parcours instanceof ApcParcours) {
            return null;
        }

        return $sae->isGoodParcours($parcours) ? $parcours : null;
    }
}
