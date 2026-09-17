<?php

namespace App\Command;

use App\Entity\ApcParcours;
use App\Repository\VersionRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:export-version-xlsx',
    description: 'Génère le fichier XLSX des départements d\'une version donnée.',
)]
class ExportVersionXlsxCommand extends Command
{
    public function __construct(
        private readonly VersionRepository $versionRepository,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('annee', InputArgument::OPTIONAL, 'Année de la version à exporter (2027 par défaut)', 2027)
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Chemin du fichier XLSX de sortie');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $annee = (int) $input->getArgument('annee');

        $versions = $this->versionRepository->findBy(['annee' => $annee]);
        if ($versions === []) {
            $io->error(sprintf('Aucune version trouvée pour l\'année %d.', $annee));

            return Command::FAILURE;
        }

        usort($versions, static function ($a, $b): int {
            $departementA = $a->getDepartement();
            $departementB = $b->getDepartement();

            return ($departementA?->getNumeroAnnexe() ?? 0) <=> ($departementB?->getNumeroAnnexe() ?? 0);
        });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('departement');

        $headers = ['libelle', 'sigle', 'numero_annexe', 'parcours1', 'parcours2', 'parcours3', 'parcours4', 'parcours5'];
        $sheet->fromArray([$headers], null, 'A1');

        $line = 2;
        foreach ($versions as $version) {
            $departement = $version->getDepartement();
            $parcours = $version->getApcParcours()->toArray();
            usort($parcours, static fn (ApcParcours $a, ApcParcours $b): int => ($a->getOrdre() ?? 0) <=> ($b->getOrdre() ?? 0));

            $row = [
                $departement?->getLibelle(),
                $departement?->getSigle(),
                $departement?->getNumeroAnnexe(),
            ];

            for ($i = 0; $i < 5; ++$i) {
                if (array_key_exists($i, $parcours)) {
                    $row[] = $parcours[$i]?->getLibelle() ?? '';
                }
            }

            $sheet->fromArray([$row], null, 'A' . $line);
            ++$line;
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $outputPath = $input->getOption('output');
        if ($outputPath === null || trim((string) $outputPath) === '') {
            $outputDir = $this->kernel->getProjectDir() . '/public/upload/xlsx';
            if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new RuntimeException(sprintf('Le dossier "%s" est introuvable et n\'a pas pu être créé.', $outputDir));
            }
            $outputPath = $outputDir . '/departements-' . $annee . '.xlsx';
        }

        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
            throw new RuntimeException(sprintf('Le dossier "%s" est introuvable et n\'a pas pu être créé.', $outputDir));
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        $io->success(sprintf('Fichier XLSX généré pour l\'année %d : %s', $annee, $outputPath));

        return Command::SUCCESS;
    }
}
