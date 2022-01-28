<?php

namespace App\Command;

use App\Classes\Apc\ApcStructure;
use App\Classes\Latex\GenereFile;
use App\Repository\DepartementRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

#[AsCommand(
    name: 'app:genere-latex',
    description: 'Genere le fichier latex pour la spécialité',
)]
class GenereLatexCommand extends Command
{


    public function __construct(
        private GenereFile $genereFile,
        private DepartementRepository $departementRepository,
        private ApcStructure $apcStructure,
        private KernelInterface $kernel,
        private Environment $environment
    ) {

        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Sigle de la spécialité');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $departement = $this->departementRepository->findOneBy(['sigle' => $arg1]);
            if ($departement === null) {
                $io->error('Département inexistant');

                return Command::FAILURE;
            }
            $this->genereFile->genereFile($departement, $this->kernel->getProjectDir() . '/public/latex/');

        }


        $io->success('Fichier généré mise à jour.');

        return Command::SUCCESS;
    }
}
