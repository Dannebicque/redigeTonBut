<?php


namespace App\Classes\Latex;


use App\Classes\Apc\ApcStructure;
use App\Entity\Departement;
use App\Entity\Version;
use App\Latex\LatexSanitizer;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twig\Environment;

class GenereFile
{
    /**
     * @var ApcRessourceRepository
     */
    public ApcRessourceRepository $apcRessourceRepository;
    /**
     * @var ApcRessourceParcoursRepository
     */
    public ApcRessourceParcoursRepository $apcRessourceParcoursRepository;
    /**
     * @var ApcSaeRepository
     */
    public ApcSaeRepository $apcSaeRepository;
    /**
     * @var ApcSaeParcoursRepository
     */
    public ApcSaeParcoursRepository $apcSaeParcoursRepository;
    protected Departement $departement;

    protected Environment $twig;

    protected ApcStructure $apcStructure;

    protected string $chemin;

    private LatexSanitizer $latexSanitizer;

    private string $projectDir;


    public function __construct(
        ApcRessourceRepository $apcRessourceRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcStructure $apcStructure,
        Environment $twig,
        LatexSanitizer $latexSanitizer,
        string $projectDir,
    ) {
        $this->apcStructure = $apcStructure;
        $this->twig = $twig;
        $this->apcRessourceRepository = $apcRessourceRepository;
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->apcSaeParcoursRepository = $apcSaeParcoursRepository;
        $this->latexSanitizer = $latexSanitizer;
        $this->projectDir = $projectDir;
    }

    public function renderContent(Version $version, bool $includeAssets = true): string
    {
        return $this->latexSanitizer->normalizeLatexDocument(
            $this->twig->render('latex/annexe_specialite.tex.twig', $this->buildRenderContext($version, $includeAssets))
        );
    }

    /**
     * @return array<string, string> [archivePath => absoluteSourcePath]
     */
    public function getArchiveAssets(Version $version, bool $includeAssets = true): array
    {
        if ($includeAssets === false) {
            return [];
        }

        $departement = $version->getDepartement();
        if (!$departement instanceof Departement) {
            throw new \RuntimeException('Département introuvable pour l’export LaTeX.');
        }

        $latexRoot = $this->resolveLatexRoot();
        $archiveAssets = [];

        $this->addAssetFile(
            $archiveAssets,
            $latexRoot . '/PN-BUT.cls',
            'PN-BUT.cls',
            true,
        );

        $this->addAssetFile(
            $archiveAssets,
            $latexRoot . '/couverture/couv_' . $departement->getNumeroAnnexe() . '.pdf',
            'couverture/couv_' . $departement->getNumeroAnnexe() . '.pdf',
            true,
        );

        $annexeRoot = $latexRoot . '/' . $departement->getNumeroAnnexe();
        $this->addAssetDirectory($archiveAssets, $annexeRoot . '/ref-competences', $departement->getNumeroAnnexe() . '/ref-competences');
        $this->addAssetDirectory($archiveAssets, $annexeRoot . '/tableaux', $departement->getNumeroAnnexe() . '/tableaux');

        return $archiveAssets;
    }

    public function genereFile(Version $version, string $chemin, bool $includeAssets = true): string
    {
        $departement = $version->getDepartement();
        $name = rtrim($chemin, '/') . '/PN-BUT-' . $departement->getSigle() . '.tex';
        $fichier = fopen($name, 'wb+');
        $content = $this->renderContent($version, $includeAssets);
        $content="\xEF\xBB\xBF".$content;
        fwrite($fichier, $content);
        fclose($fichier);

        return $name;

    }

    /**
     * @return array<string, mixed>
     */
    private function buildRenderContext(Version $version, bool $includeAssets = true): array
    {
        $parcours = $version->getApcParcours();
        $tParcours = $this->apcStructure->parcoursNiveaux($version);
        $competences = $version->getApcCompetences();
        $departement = $version->getDepartement();

        if (!$departement instanceof Departement) {
            throw new \RuntimeException('Département introuvable pour l’export LaTeX.');
        }

        $tComp = [];
        foreach ($competences as $comp) {
            $tComp[$comp->getId()] = $comp;
        }

        $competencesParcours = [];
        $tSemestres = [];

        foreach ($tParcours as $key => $parc) {
            $tSemestres[$key] = [];
            $competencesParcours[$key] = [];
            foreach ($parc as $k => $v) {
                if (isset($tComp[$k])) {
                    $competencesParcours[$key][] = $tComp[$k];
                }
            }
        }

        $semestres = $version->getSemestres();
        $ressources = [];
        $saes = [];

        if ($departement->getTypeStructure() === Departement::TYPE3) {
            foreach ($semestres as $semestre) {
                $semestreParcours = $semestre->getApcParcours();
                if ($semestreParcours === null || $semestreParcours->getId() === null) {
                    continue;
                }

                $parcoursId = $semestreParcours->getId();
                $tSemestres[$parcoursId][$semestre->getOrdreLmd()] = [
                    'semestre' => $semestre,
                    'saes' => $this->apcSaeParcoursRepository->findBySemestre($semestre, $semestreParcours),
                    'ressources' => $this->apcRessourceParcoursRepository->findBySemestre($semestre, $semestreParcours),
                ];
            }
        } else {
            foreach ($semestres as $semestre) {
                if ($semestre->getOrdreLmd() > 2) {
                    foreach ($parcours as $parcour) {
                        $ressources[$semestre->getId()][$parcour->getId()] = $this->apcRessourceParcoursRepository->findBySemestreArray($semestre, $parcour);
                        $saes[$semestre->getId()][$parcour->getId()] = $this->apcSaeParcoursRepository->findBySemestreArray($semestre, $parcour);
                    }
                } else {
                    $ressources[$semestre->getId()] = $this->apcRessourceRepository->findBySemestreArray($semestre);
                    $saes[$semestre->getId()] = $this->apcSaeRepository->findBySemestreArray($semestre);
                }
            }
        }
dump($includeAssets);
        return [
            'departement' => $departement,
            'version' => $version,
            'competencesParcours' => $competencesParcours,
            'semestres' => $tSemestres,
            'saes' => $saes,
            'ressources' => $ressources,
            'includeAssets' => $includeAssets,
        ];
    }

    private function resolveLatexRoot(): string
    {
        foreach ([$this->projectDir . '/public/latex', $this->projectDir . '/public-local/latex'] as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('Répertoire des assets LaTeX introuvable (public/latex ou public-local/latex).');
    }

    /**
     * @param array<string, string> $archiveAssets
     */
    private function addAssetFile(array &$archiveAssets, string $sourcePath, string $archivePath, bool $required = false): void
    {
        if (!is_file($sourcePath)) {
            if ($required) {
                throw new \RuntimeException(sprintf('Asset LaTeX introuvable : %s', $sourcePath));
            }

            return;
        }

        $archiveAssets[$archivePath] = $sourcePath;
    }

    /**
     * @param array<string, string> $archiveAssets
     */
    private function addAssetDirectory(array &$archiveAssets, string $sourceDirectory, string $archiveDirectory): void
    {
        if (!is_dir($sourceDirectory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDirectory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filename = $file->getFilename();
            if ($filename === '.DS_Store') {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen($sourceDirectory) + 1);
            $archiveAssets[$archiveDirectory . '/' . str_replace('\\', '/', $relativePath)] = $file->getPathname();
        }
    }
}
