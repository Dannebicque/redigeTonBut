<?php

namespace App\Command;

use App\Classes\PN\Competences\GenerePdfCompetences;
use App\Repository\DepartementRepository;
use App\Repository\VersionRepository;
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
        protected VersionRepository $versionRepository,
        protected DepartementRepository $departementRepository,
        protected GenerePdfCompetences $generePdfCompetences
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('specialite', InputArgument::OPTIONAL, 'Nom de la spécialité ou "all"', 'all')
            ->addArgument('version', InputArgument::OPTIONAL, 'Version du PN', 2027);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('specialite');

        if ($arg1 === 'all') {
            $io->note('Génération pour toutes les spécialités');
            $versions = $this->versionRepository->findBy(['annee' => (int) $input->getArgument('version')]);
            foreach ($versions as $version) {
                $departement = $version->getDepartement();
                if ($departement === null) {
                    continue;
                }

                $io->note(sprintf('Génération pour la spécialité %s', $departement->getLibelle()));
                $this->generePdfCompetences->generePdfCompetencesParPage($version);
                $this->generePdfCompetences->generePdfCompetencesComplet($version);
            }
        } else {
            $io->note(sprintf('Génération pour la spécialité %s', $arg1));
            $specialite = $this->departementRepository->findOneBy(['sigle' => $arg1]);
            if ($specialite !== null) {
                $version = $this->versionRepository->findOneBy(['annee' => $input->getArgument('version'), 'departement' => $specialite->getId()]);
                if ($version === null) {
                    $io->error('Version introuvable pour cette spécialité.');

                    return Command::FAILURE;
                }

                $this->generePdfCompetences->generePdfCompetencesParPage($version);
                $this->generePdfCompetences->generePdfCompetencesComplet($version);
            } else {
                $io->error('Spécialité inexistante.');

                return Command::FAILURE;
            }
        }


        $io->success('Les référentiels de compétences ont été générés.');

        return Command::SUCCESS;
    }
}
