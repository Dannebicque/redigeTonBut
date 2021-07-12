<?php

namespace App\Command;

use App\Repository\DepartementRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-codification',
    description: 'Mise à jour de la codification de la spécialité',
)]
class UpdateCodificationCommand extends Command
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


    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Sigle de la spécialité')
        ;
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

           foreach ($departement->getApcCompetences() as $competence) {
               foreach ($competence->getApcComposanteEssentielles() as $compEss)
               {
                   $compEss->setCode(Codification::codeComposanteEssentielle($compEss));
               }

               foreach ($competence->getApcNiveaux() as $apcNiveau)
               {
                   foreach ($apcNiveau->getApcApprentissageCritiques() as $ac) {
                       $ac->setCode(Codification::codeApprentissageCritique($ac));
                   }
               }
           }

           /** @var \App\Entity\Semestre $semestre */
            foreach ($departement->getSemestres() as $semestre) {
               foreach ($semestre->getApcRessources() as $r)
               {
                   $r->setCodeMatiere(Codification::codeRessource($r));
               }

                foreach ($semestre->getApcSaes() as $r)
                {
                    $r->setCodeMatiere(Codification::codeSae($r));
                }
           }
            $this->entityManager->flush();
        }



        $io->success('Codification mise à jour.');

        return Command::SUCCESS;
    }
}
