<?php

declare(strict_types=1);

namespace App\Pdf\Builder;

use App\Classes\Apc\TableauCroise;
use App\Classes\Tableau\Structure;
use App\Classes\Tableau\VolumesHoraires;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Entity\Version;
use App\Pdf\PdfPayloadBuilderInterface;
use App\Pdf\PdfSourceType;
use App\Pdf\RemotePdfRequest;
use App\Repository\ApcParcoursRepository;
use App\Repository\SemestreRepository;
use App\Repository\VersionRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

final readonly class TableauxSynthesePdfPayloadBuilder implements PdfPayloadBuilderInterface
{
    public const DOCUMENT_KEY_STRUCTURE = 'tableau_structure';
    public const DOCUMENT_KEY_CROISE = 'tableau_croise';
    public const DOCUMENT_KEY_SYNTHESE = 'tableaux_synthese';

    public function __construct(
        private VersionRepository $versionRepository,
        private SemestreRepository $semestreRepository,
        private ApcParcoursRepository $parcoursRepository,
        private Structure $structure,
        private TableauCroise $tableauCroise,
        private VolumesHoraires $volumesHoraires,
        private Environment $twig,
        private KernelInterface $kernel,
    ) {
    }

    public function supports(PdfSourceType $sourceType, string $documentKey): bool
    {
        return $sourceType === PdfSourceType::REFERENTIEL
            && in_array($documentKey, [
                self::DOCUMENT_KEY_STRUCTURE,
                self::DOCUMENT_KEY_CROISE,
                self::DOCUMENT_KEY_SYNTHESE,
                'tableau-structure',
                'tableau-croise',
                'tableau_synthese',
            ], true);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function build(string $sourceId, string $documentKey, array $parameters = []): RemotePdfRequest
    {
        $version = $this->versionRepository->find($sourceId);

        if (!$version instanceof Version) {
            throw new \RuntimeException('Version de référentiel introuvable.');
        }

        if (in_array($documentKey, [self::DOCUMENT_KEY_CROISE, 'tableau-croise'], true)) {
            return $this->buildTableauCroise($version, $documentKey, $parameters);
        }

        if ($documentKey === self::DOCUMENT_KEY_SYNTHESE || $documentKey === 'tableau_synthese') {
            $type = $parameters['type'] ?? $parameters['tableau'] ?? 'structure';
            if ($type === 'croise' || $type === self::DOCUMENT_KEY_CROISE) {
                return $this->buildTableauCroise($version, $documentKey, $parameters);
            }
        }

        return $this->buildTableauStructure($version, $documentKey, $parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function buildTableauStructure(Version $version, string $documentKey, array $parameters): RemotePdfRequest
    {
        $parcoursId = $parameters['parcoursId'] ?? $parameters['parcours'] ?? null;
        $parcours = null;
        if ($parcoursId !== null && $parcoursId !== '') {
            $parcours = $this->parcoursRepository->find($parcoursId);
        }

        $departement = $version->getDepartement();
        if ($departement?->getTypeStructure() === Departement::TYPE3 && $parcours instanceof ApcParcours) {
            $semestres = $this->semestreRepository->findByParcours($parcours);
        } else {
            $semestres = $version->getSemestres();
        }

        $json = $this->structure->setSemestres($semestres)->setVersion($version)->getDataJson();
        if (!isset($json['departement']) && isset($json['version'])) {
            $json['departement'] = $json['version'];
        }

        $html = $this->twig->render('pdf/tableau-structure.html.twig', [
            'departement' => $departement,
            'version' => $version,
            'donnees' => $json,
            'parcours' => $parcours,
            'parameters' => $parameters,
        ]);

        $filename = $parcours instanceof ApcParcours
            ? sprintf('tableau-structure-%s.pdf', $parcours->getId())
            : 'tableau-structure.pdf';

        return new RemotePdfRequest(
            type: 'html',
            options: [
                'filename' => $filename,
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
            filename: $filename,
            sourceHash: hash('sha256', json_encode([
                'type' => 'html',
                'documentKey' => $documentKey,
                'html' => $html,
                'parameters' => $parameters,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
        );
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function buildTableauCroise(Version $version, string $documentKey, array $parameters): RemotePdfRequest
    {
        $semestreId = $parameters['semestreId'] ?? $parameters['semestre'] ?? null;
        if ($semestreId === null || $semestreId === '') {
            throw new \RuntimeException('Identifiant du semestre manquant pour le tableau croisé.');
        }

        $semestre = $this->semestreRepository->find($semestreId);
        if (!$semestre instanceof Semestre) {
            throw new \RuntimeException('Semestre introuvable pour le tableau croisé.');
        }

        $parcoursId = $parameters['parcoursId'] ?? $parameters['parcours'] ?? null;
        $parcours = null;
        if ($parcoursId !== null && $parcoursId !== '') {
            $parcours = $this->parcoursRepository->find($parcoursId);
        }

        $departement = $version->getDepartement();
        $annee = $semestre->getAnnee();

        if ($departement?->getTypeStructure() === Departement::TYPE3 && $parcours instanceof ApcParcours) {
            $semestres = $this->semestreRepository->findBy([
                'annee' => $annee?->getId(),
                'apcParcours' => $parcours->getId(),
            ]);
        } elseif ($annee !== null) {
            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId()]);
        } else {
            $semestres = [$semestre];
        }

        if ($parcours instanceof ApcParcours) {
            $this->tableauCroise->getDatas($semestre, $parcours);
            $donnees = $this->volumesHoraires->setSemestres($semestres, $parcours)->getDataJson();
            $defaultFilename = sprintf('tableau-croise-%s-%s.pdf', $semestre->getId(), $parcours->getId());
        } else {
            $this->tableauCroise->getDatas($semestre);
            $donnees = $this->volumesHoraires->setSemestres($semestres)->getDataJson();
            $defaultFilename = sprintf('tableau-croise-%s.pdf', $semestre->getId());
        }

        $images = $this->buildImages($this->tableauCroise->getRessources(), $this->tableauCroise->getSaes(), $departement);

        $html = $this->twig->render('pdf/tableau-croise.html.twig', [
            'linuxpath' => '',
            'departement' => $departement,
            'donnees' => $donnees,
            'semestre' => $semestre,
            'niveaux' => $this->tableauCroise->getNiveaux(),
            'saes' => $this->tableauCroise->getSaes(),
            'ressources' => $this->tableauCroise->getRessources(),
            'tab' => $this->tableauCroise->getTab(),
            'coefficients' => $this->tableauCroise->getCoefficients(),
            'parcours' => $parcours,
            'saeImages' => $images['saeImages'],
            'ressourceImages' => $images['ressourceImages'],
            'parameters' => $parameters,
        ]);

        $filename = (isset($parameters['filename']) && is_string($parameters['filename']) && $parameters['filename'] !== '')
            ? $parameters['filename']
            : $defaultFilename;

        return new RemotePdfRequest(
            type: 'html',
            options: [
                'filename' => $filename,
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
                'images' => [
                    'ressources' => $images['ressourceImages'],
                    'saes' => $images['saeImages'],
                ],
                'assets' => $images['assets'],
            ],
            filename: $filename,
            sourceHash: hash('sha256', json_encode([
                'type' => 'html',
                'documentKey' => $documentKey,
                'html' => $html,
                'parameters' => $parameters,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
        );
    }

    /**
     * @param iterable<object> $ressources
     * @param iterable<object> $saes
     * @return array{
     *     ressourceImages: array<int, string>,
     *     saeImages: array<int, string>,
     *     assets: array<string, string>,
     * }
     */
    private function buildImages(iterable $ressources, iterable $saes, ?Departement $departement): array
    {
        $ressourceImages = [];
        $saeImages = [];
        $assets = [];

        $projectDir = $this->kernel->getProjectDir();
        $font = $projectDir . '/public/arial.ttf';
        if (!file_exists($font)) {
            $font = $projectDir . '/public-local/arial.ttf';
        }

        $targetDir = null;
        $numeroAnnexe = $departement?->getNumeroAnnexe();
        if ($numeroAnnexe !== null) {
            $targetDir = $projectDir . '/public/latex/' . $numeroAnnexe . '/tableaux';
            if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                $targetDir = null;
            }
        }

        if (function_exists('imagecreate') && file_exists($font)) {
            foreach ($ressources as $ressource) {
                $texte = $ressource->getCodeMatiere() . ' ' . $ressource->getLibelle();
                $size = strlen($texte) < 30 ? 10 : 8;
                $texte = $this->adaptTexte($texte, $size);

                $im = imagecreate(50, 200);
                if ($im !== false) {
                    $fond = imagecolorallocate($im, 255, 255, 255);
                    $noir = imagecolorallocate($im, 0, 0, 0);
                    imagefill($im, 0, 0, $fond);
                    imagettftext($im, $size, 90, 15, 190, $noir, $font, $texte);

                    ob_start();
                    imagepng($im);
                    $imageData = (string) ob_get_clean();
                    $base64Data = base64_encode($imageData);
                    $ressourceImages[$ressource->getId()] = 'data:image/png;base64,' . $base64Data;
                    $assets['ressource_' . $ressource->getId() . '.png'] = $base64Data;
                    if ($numeroAnnexe !== null) {
                        $assets['latex/' . $numeroAnnexe . '/tableaux/ressource_' . $ressource->getId() . '.png'] = $base64Data;
                    }

                    if ($targetDir !== null && is_dir($targetDir)) {
                        @file_put_contents($targetDir . '/ressource_' . $ressource->getId() . '.png', $imageData);
                    }

                    imagedestroy($im);
                }
            }

            foreach ($saes as $sae) {
                $texte = $sae->getCodeMatiere() . ' ' . $sae->getLibelle();
                $size = strlen($texte) < 30 ? 10 : 8;
                $texte = $this->adaptTexte($texte, $size);

                $im = imagecreate(50, 200);
                if ($im !== false) {
                    $fond = imagecolorallocate($im, 173, 216, 230);
                    $noir = imagecolorallocate($im, 0, 0, 0);
                    imagefill($im, 0, 0, $fond);
                    imagettftext($im, 10, 90, 15, 190, $noir, $font, $texte);

                    ob_start();
                    imagepng($im);
                    $imageData = (string) ob_get_clean();
                    $base64Data = base64_encode($imageData);
                    $saeImages[$sae->getId()] = 'data:image/png;base64,' . $base64Data;
                    $assets['sae_' . $sae->getId() . '.png'] = $base64Data;
                    if ($numeroAnnexe !== null) {
                        $assets['latex/' . $numeroAnnexe . '/tableaux/sae_' . $sae->getId() . '.png'] = $base64Data;
                    }

                    if ($targetDir !== null && is_dir($targetDir)) {
                        @file_put_contents($targetDir . '/sae_' . $sae->getId() . '.png', $imageData);
                    }

                    imagedestroy($im);
                }
            }
        }

        return [
            'ressourceImages' => $ressourceImages,
            'saeImages' => $saeImages,
            'assets' => $assets,
        ];
    }

    private function adaptTexte(string $texte, int $size): string
    {
        if ($size === 10) {
            return wordwrap($texte, 28, "\n", false);
        }

        return wordwrap($texte, 35, "\n", false);
    }
}
