<?php

namespace App\Command;

use App\Classes\GenereMatrix;
use App\Repository\DepartementRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:genere-matrix',
    description: 'Génére un fichier matrix pour Mahara',
)]
class GenereMatrixCommand extends Command
{

    public function __construct(
        protected DepartementRepository $departementRepository,
        protected GenereMatrix $genereMatrix,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('specialite', InputArgument::OPTIONAL, 'Spécialité')
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('specialite');

        if ($arg1 === '' or $arg1 === null) {
            $io->error(sprintf('Aucune spécialité précisée'));
        }

        $departement = $this->departementRepository->findOneBy(['sigle' => $arg1]);

        if ($departement === null) {
            $io->error(sprintf('Spécialité %s non trouvée', $arg1));
        }

        $this->genereMatrix->genereSpecialite($departement);


        $io->success('Fichier généré avec succès.');

        return Command::SUCCESS;
    }
}
