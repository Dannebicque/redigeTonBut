<?php

namespace App\Command;

use App\Classes\PN\Competences\GenerePdfCompetences;
use App\Repository\DepartementRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:genere-ref-competence',
    description: 'Genere les pages du référentiel de compétences',
)]
class GenereRefCompetenceCommand extends Command
{

    public function __construct(
        protected DepartementRepository $departementRepository,
        protected GenerePdfCompetences $generePdfCompetences
    ) {
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

        if ($arg1 === 'all') {
            $io->note(sprintf('Génération pour toutes les spécialités'));
            //toutes les spécialites
            $specialites = $this->departementRepository->findAll();
            foreach ($specialites as $specialite) {
                $io->note(sprintf('Génération pour la spécialité %s', $specialite->getLibelle()));
                $this->generePdfCompetences->generePdfCompetencesParPage($specialite);
                $this->generePdfCompetences->generePdfCompetencesComplet($specialite);
            }
        } else {
            $io->note(sprintf('Génération pour la spécialité %s', $arg1));
            //une spécialité
            $specialite = $this->departementRepository->findOneBy(['sigle' => $arg1]);
            if ($specialite !== null) {
                $this->generePdfCompetences->generePdfCompetencesParPage($specialite);
                $this->generePdfCompetences->generePdfCompetencesComplet($specialite);
            } else {
                $io->error('Spécialité inexistante.');

                return Command::FAILURE;
            }
        }


        $io->success('Les référentiels de compétences ont été générés.');

        return Command::SUCCESS;
    }
}
