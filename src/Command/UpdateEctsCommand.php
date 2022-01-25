<?php

namespace App\Command;

use App\Entity\Departement;
use App\Repository\ApcCompetenceSemestreRepository;
use App\Repository\DepartementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-ects',
    description: 'Découpage des ECTS par parcours pour les types 1 et 2',
)]
class UpdateEctsCommand extends Command
{

    public function __construct(
        private DepartementRepository $departementRepository,
        private EntityManagerInterface $entityManager,
        private ApcCompetenceSemestreRepository $apcCompetenceSemestreRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $departements = $this->departementRepository->findAll();

        foreach ($departements as $departement)
        {
            if ($departement->getTypeStructure() !== Departement::TYPE3) {
                $allParcours = $departement->getApcParcours();
                foreach ($departement->getSemestres() as $semestre) {
                    if ($semestre->getOrdreLmd() > 2) {
                        $competences = $this->apcCompetenceSemestreRepository->findBy(['semestre' => $semestre]);
                        foreach ($competences as $competence) {
                            $ects = $competence->getECTS();
                            $t = [];
                            foreach ($allParcours as $parcours) {
                                $t[$parcours->getId()] = $ects;
                            }
                            $competence->setEctsParcours($t);
                        }
                    }

                }

            }
        }
        $this->entityManager->flush();

        $io->success('Données mises à jour.');

        return Command::SUCCESS;
    }
}
