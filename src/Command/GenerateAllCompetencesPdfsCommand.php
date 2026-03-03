<?php
namespace App\Command;

use App\Classes\Export\CompetencesExport;
use App\Repository\DepartementRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:generate-competences-pdfs',
    description: 'Génère localement les PDF de référentiel pour tous les départements',
)]
class GenerateAllCompetencesPdfsCommand extends Command
{
    private string $projectDir = '';
    public function __construct(
        private CompetencesExport $competencesExport,
        private DepartementRepository $departementRepository,
        KernelInterface $projectDir
    ) {
        parent::__construct();
        $this->projectDir = $projectDir->getProjectDir();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Répertoire de sortie', $this->projectDir . '/var/export/pdfs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outDir = rtrim($input->getOption('dir'), "/\\");
        $fs = new Filesystem();
        $fs->mkdir($outDir, 0755);

        $departements = $this->departementRepository->findAll();
        if (empty($departements)) {
            $io->warning('Aucun département trouvé.');
            return Command::SUCCESS;
        }

        $io->title('Génération des PDF de compétences');
        $count = count($departements);
        $io->progressStart($count);
        $errors = 0;

        foreach ($departements as $departement) {
            try {
                $pdfResponse = $this->competencesExport->generePdfVersionCompetences($departement);
                $content = $pdfResponse->getContent();

                $sigle = (string) $departement->getSigle();
                $safeSigle = preg_replace('#[^A-Za-z0-9_\-]#', '_', $sigle) ?: (string) $departement->getId();
                $filename = 'referentiel-competence-version-' . $safeSigle . '.pdf';
                $filePath = $outDir . DIRECTORY_SEPARATOR . $filename;

                if (false === @file_put_contents($filePath, $content)) {
                    throw new \RuntimeException('Impossible d\'écrire le fichier ' . $filePath);
                }
            } catch (\Throwable $e) {
                $io->error('Échec pour département ' . ($departement->getId() ?? '?') . ' : ' . $e->getMessage());
                $errors++;
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        if ($errors > 0) {
            $io->warning("$errors erreurs rencontrées (voir messages ci‑dessus).");
        } else {
            $io->success("Tous les PDF ont été générés dans : `{$outDir}`");
        }

        return Command::SUCCESS;
    }
}
