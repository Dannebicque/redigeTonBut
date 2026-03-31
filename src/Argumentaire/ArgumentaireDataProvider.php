<?php

declare(strict_types=1);

namespace App\Argumentaire;

use App\Classes\Tableau\Structure;
use App\Entity\Version;
use App\Repository\SemestreRepository;

final class ArgumentaireDataProvider
{
    public function __construct(
        private readonly SemestreRepository $semestreRepository,
        private readonly Structure $structure,
    ) {
    }

    /**
     * @return array{
     *     previousVersion: ?Version,
     *     structureCurrent: array<int|string, mixed>,
     *     structurePrevious: array<int|string, mixed>
     * }
     */
    public function getData(Version $version): array
    {
        $previousVersion = $version->getPreviousVersion();

        return [
            'previousVersion' => $previousVersion,
            'structureCurrent' => $this->buildStructureData($version),
            'structurePrevious' => $previousVersion instanceof Version
                ? $this->buildStructureData($previousVersion)
                : $this->createEmptyStructureData(),
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function buildStructureData(Version $version): array
    {
        $semestres = $this->semestreRepository->findByVersion($version);

        return array_replace(
            $this->createEmptyStructureData(),
            $this->structure->setSemestres($semestres)->setVersion($version)->getDataJson(),
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    private function createEmptyStructureData(): array
    {
        $emptySemestre = [
            'nbHeuresCoursProjet' => 0,
            'nbHeuresProjet' => 0,
            'nbHeuresEnseignementSaeLocale' => 0,
            'nbHeuresEnseignementRessourceNational' => 0,
            'nbHeuresEnseignementRessourceLocale' => 0,
            'nbHeuresTpNational' => 0,
            'nbHeuresTpLocale' => 0,
            'nbSemainesStageMin' => 0,
            'nbSemainesStageMax' => 0,
        ];

        $data = ['version' => []];
        foreach (range(1, 6) as $semestre) {
            $data[$semestre] = $emptySemestre;
        }

        return $data;
    }
}

