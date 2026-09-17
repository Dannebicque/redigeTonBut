<?php

declare(strict_types=1);

namespace App\Pdf\Builder;

use App\Classes\Apc\ApcStructure;
use App\Entity\Version;
use App\Pdf\PdfPayloadBuilderInterface;
use App\Pdf\PdfSourceType;
use App\Pdf\RemotePdfRequest;
use App\Repository\VersionRepository;
use Twig\Environment;

final readonly class CompetencesReferentielPdfPayloadBuilder implements PdfPayloadBuilderInterface
{
    public const DOCUMENT_KEY = 'export_referentiel_competences';

    public function __construct(
        private VersionRepository $versionRepository,
        private ApcStructure $apcStructure,
        private Environment $twig,
    ) {
    }

    public function supports(PdfSourceType $sourceType, string $documentKey): bool
    {
        return $sourceType === PdfSourceType::REFERENTIEL
            && $documentKey === self::DOCUMENT_KEY;
    }

    public function build(string $sourceId, string $documentKey, array $parameters = []): RemotePdfRequest
    {
        $version = $this->versionRepository->find($sourceId);

        if (!$version instanceof Version) {
            throw new \RuntimeException('Version de référentiel introuvable.');
        }

        $tParcours = $this->apcStructure->parcoursNiveaux($version);
        $competences = $version->getApcCompetences();
        $tComp = [];

        foreach ($competences as $competence) {
            $tComp[$competence->getId()] = $competence;
        }

        $competencesParcours = [];
        foreach ($tParcours as $key => $parc) {
            $competencesParcours[$key] = [];
            foreach ($parc as $competenceId => $value) {
                if (isset($tComp[$competenceId])) {
                    $competencesParcours[$key][] = $tComp[$competenceId];
                }
            }
        }

        $html = $this->twig->render('competences/export-referentiel.html.twig', [
            'competencesParcours' => $competencesParcours,
            'departement' => $version->getDepartement(),
            'version' => $version,
            'competences' => $competences,
            'parcours' => $version->getApcParcours(),
            'parcoursNiveaux' => $tParcours,
            'parameters' => $parameters,
        ]);

        return new RemotePdfRequest(
            type: 'html',
            options: [
                'filename' => sprintf('referentiel-competence-%s.pdf', $version->getDepartement()->getSigle()),
                'timeoutSeconds' => 120,
                'pageFormat' => 'A4',
                'orientation' => 'Landscape',
                'pageOrientation' => 'Landscape',
                'marginTop' => '10mm',
                'marginRight' => '10mm',
                'marginBottom' => '10mm',
                'marginLeft' => '10mm',
            ],
            payload: [
                'html' => $html,
            ],
            filename: sprintf('referentiel-competence-%s.pdf', $version->getDepartement()->getSigle()),
            sourceHash: hash('sha256', json_encode([
                'type' => 'html',
                'html' => $html,
                'parameters' => $parameters,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
        );
    }
}
