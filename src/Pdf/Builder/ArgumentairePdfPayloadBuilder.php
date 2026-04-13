<?php

declare(strict_types=1);

namespace App\Pdf\Builder;

use App\Argumentaire\ArgumentaireDataProvider;
use App\Entity\Version;
use App\Pdf\PdfPayloadBuilderInterface;
use App\Pdf\PdfSourceType;
use App\Pdf\RemotePdfRequest;
use App\Repository\ArgumentaireRepository;
use App\Repository\VersionRepository;
use Twig\Environment;
use ZipArchive;

final readonly class ArgumentairePdfPayloadBuilder implements PdfPayloadBuilderInterface
{
    public function __construct(
        private VersionRepository $versionRepository,
        private ArgumentaireRepository $argumentaireRepository,
        private ArgumentaireDataProvider $argumentaireDataProvider,
        private Environment $twig,
    ) {
    }

    public function supports(PdfSourceType $sourceType, string $documentKey): bool
    {
        return $sourceType === PdfSourceType::ARGUMENTAIRE && $documentKey === 'fiche';
    }

    public function build(string $sourceId, string $documentKey, array $parameters = []): RemotePdfRequest
    {
        $version = $this->versionRepository->find($sourceId);

        if (!$version instanceof Version) {
            throw new \RuntimeException('Version introuvable.');
        }

        $data = $this->argumentaireDataProvider->getData($version);
        $argumentaire = $this->argumentaireRepository->findOneByVersion($version);

        $html = $this->twig->render('pdf/argumentaire/fiche.html.twig', [
            'version' => $version,
            'previousVersion' => $data['previousVersion'],
            'structureCurrent' => $data['structureCurrent'],
            'structurePrevious' => $data['structurePrevious'],
            'argumentairePayload' => $argumentaire?->getPayload() ?? [],
            'parameters' => $parameters,
        ]);

        return new RemotePdfRequest(
            type: 'html',
            options: [
                'filename' => sprintf('argumentaire_%s.pdf', $version->getId()),
                'timeoutSeconds' => 120,
                'pageFormat' => 'A4',
                'marginTop' => '10mm',
                'marginRight' => '10mm',
                'marginBottom' => '10mm',
                'marginLeft' => '10mm',
            ],
            payload: [
                'html' => $html,
            ],
            filename: sprintf('argumentaire_%s.pdf', $version->getId()),
            sourceHash: hash('sha256', json_encode([
                'type' => 'html',
                'html' => $html,
                'parameters' => $parameters,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
        );
    }
}
