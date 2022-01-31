<?php

namespace App\Command;

use App\Repository\DepartementRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-codification-ref',
    description: 'Mise à jour de la codification des spécialités pour le référentiel de compétences',
)]
class UpdateCodificationRefCompetencesCommand extends Command
{
    private DepartementRepository $departementRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        DepartementRepository $departementRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->departementRepository = $departementRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);


        $departements = $this->departementRepository->findAll();
        foreach ($departements as $departement) {

            foreach ($departement->getApcCompetences() as $competence) {
                foreach ($competence->getApcComposanteEssentielles() as $compEss) {
                    $compEss->setCode(Codification::codeComposanteEssentielle($compEss));
                }

                foreach ($competence->getApcNiveaux() as $apcNiveau) {
                    foreach ($apcNiveau->getApcApprentissageCritiques() as $ac) {
                        $ac->setCode(Codification::codeApprentissageCritique($ac));
                    }
                }
            }

            $this->entityManager->flush();
            $io->success(sprintf('Mise à jour de la codification pour le département %s', $departement->getSigle()));
        }


        $io->success('Codification mise à jour.');

        return Command::SUCCESS;
    }
}
