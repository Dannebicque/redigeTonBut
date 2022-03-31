<?php

namespace App\Command;

use App\Classes\PN\GenerePdfTableaux;
use App\Repository\DepartementRepository;
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
        protected GenerePdfTableaux $generePdfTableaux
    ) {
        $this->dir = $kernel->getProjectDir() . '/public/latex/';
        $this->filesystem = new Filesystem();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('specialite', InputArgument::OPTIONAL, 'Nom de la spécialité');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('specialite');


        $io->note(sprintf('Génération pour la spécialité %s', $arg1));
        //une spécialité
        $specialite = $this->departementRepository->findOneBy(['sigle' => $arg1]);
        if ($specialite !== null) {
            $this->filesystem->exists($this->dir.$specialite->getNumeroAnnexe().'/tableaux/') ?: $this->filesystem->mkdir($this->dir.$specialite->getNumeroAnnexe().'/tableaux/');

            $this->generePdfTableaux->genereTableauStructure($specialite);
            $this->generePdfTableaux->genereTableauCroise($specialite);
        } else {
            $io->error('Spécialité inexistante.');

            return Command::FAILURE;
        }


        $io->success('Les tableaux ont été générés.');

        return Command::SUCCESS;
    }
}
