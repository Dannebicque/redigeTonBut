<?php

namespace App\Command;

use App\Classes\PN\GenerePdfTableaux;
use App\Entity\Version;
use App\Repository\DepartementRepository;
use App\Repository\VersionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:genere-tableaux',
    description: 'Generes les tableaux pour le référentiel de formation',
)]
class GenereTableauxCommand extends Command
{
    private string $dir;

    private Filesystem $filesystem;

    public function __construct(
        KernelInterface $kernel,
        protected DepartementRepository $departementRepository,
        protected VersionRepository $versionRepository,
        protected GenerePdfTableaux $generePdfTableaux
    ) {
        $this->dir = $kernel->getProjectDir() . '/public/latex/';
        $this->filesystem = new Filesystem();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('specialite', InputArgument::OPTIONAL, 'Nom de la spécialité')
            ->addArgument('version', InputArgument::OPTIONAL, 'Année de version du PN', 2027);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('specialite');
        $year = (int) $input->getArgument('version');

        if (is_string($arg1) && $arg1 !== '') {
            $specialite = $this->departementRepository->findOneBy(['sigle' => $arg1]);
            if ($specialite === null) {
                $io->error(sprintf('Spécialité "%s" inexistante.', $arg1));

                return Command::FAILURE;
            }

            $version = $this->versionRepository->findOneBy([
                'annee' => $year,
                'departement' => $specialite->getId(),
            ]);

            if ($version === null) {
                $io->error(sprintf('Version %d introuvable pour la spécialité %s.', $year, $arg1));

                return Command::FAILURE;
            }

            $this->generateForVersion($version, $io);
            $io->success(sprintf('Les tableaux de la spécialité %s pour la version %d ont été générés.', $arg1, $year));

            return Command::SUCCESS;
        }

        $versions = $this->versionRepository->findBy(['annee' => $year]);
        usort($versions, static function ($left, $right): int {
            $leftAnnexe = $left->getDepartement()?->getNumeroAnnexe() ?? 0;
            $rightAnnexe = $right->getDepartement()?->getNumeroAnnexe() ?? 0;

            return $leftAnnexe <=> $rightAnnexe;
        });

        if ($versions === []) {
            $io->warning(sprintf('Aucune version trouvée pour l’année %d.', $year));

            return Command::FAILURE;
        }

        foreach ($versions as $version) {
            if ($version->getDepartement() === null) {
                continue;
            }

            $this->generateForVersion($version, $io);
        }

        $io->success(sprintf('%d tableaux ont été générés pour l’année %d.', count($versions), $year));

        return Command::SUCCESS;
    }

    private function generateForVersion(Version $version, SymfonyStyle $io): void
    {
        $departement = $version->getDepartement();
        if ($departement === null) {
            return;
        }

        $this->filesystem->mkdir($this->dir . $departement->getNumeroAnnexe() . '/tableaux/', 0777, true);
        $this->filesystem->mkdir($this->dir . $departement->getNumeroAnnexe() . '/tableaux/pdf', 0777, true);
        $io->note(sprintf('Génération pour la spécialité %s [%s]', $departement->getLibelle(), $departement->getNumeroAnnexe()));

        $this->generePdfTableaux->genereTableauStructure($version);
        $this->generePdfTableaux->genereTableauCroise($version);
    }
}
