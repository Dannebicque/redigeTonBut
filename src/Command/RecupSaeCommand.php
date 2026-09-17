<?php

namespace App\Command;

use App\Entity\Version;
use App\Repository\ApcSaeRepository;
use App\Repository\DepartementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recup-sae',
    description: 'Add a short description for your command',
)]
class RecupSaeCommand extends Command
{
    public function __construct(
        protected DepartementRepository $departementRepository,
        protected ApcSaeRepository $apcSaeRepository,
        protected EntityManagerInterface $entityManager
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

        // récupère les SAE de la version 2021 pour recopier objectifs et heures projets dans 2027, en sa basant sur le code de la SAE

        $departements = $this->departementRepository->findAll();
        foreach ($departements as $departement) {
            $io->section('Departement : ' . $departement->getSigle());
            // SAE version 2021
            $version2021 = $this->getVersion2021($departement);
            $saes2021 = $this->apcSaeRepository->findByVersion($version2021);
            $t = [];
            foreach ($saes2021 as $sae2021) {
                $t[$sae2021->getCleUnique()] = $sae2021;
                $io->info('SAE '. $sae2021->getCodeMatiere());
                $io->info('SAE '. $sae2021->getCleUnique());
            }

            $io->info('Nombre de SAE 2021 : ' . count($saes2021));
            $match = 0;

            $version2027 = $this->getVersion2027($departement);
            $saes2027 = $this->apcSaeRepository->findByVersion($version2027);
            foreach ($saes2027 as $sae2027) {
                //récupération de la clé en 2021 pour comparaison
                if (isset($t[$sae2027->getCleUnique()])) {
                    $sae2027->setObjectifs($t[$sae2027->getCleUnique()]->getObjectifs() ?? '');
                    $sae2027->setProjetPpn($t[$sae2027->getCleUnique()]->getProjetPpn());
                    $match++;
                } else {
                    $io->error('SAE '. $sae2027->getCodeMatiere() . ' introuvable dans la version 2027');
                    $io->info('SAE '. $sae2027->getCleUnique());
                }
            }

            $io->info('Nombre de SAE 2027 : ' . count($saes2027));
            $io->info('Nombre de SAE correspondant : ' . $match);
            $this->entityManager->flush();
        }


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    private function getVersion2021(mixed $departement): Version
    {
        foreach ($departement->getVersions() as $version) {
            if ($version->getAnnee() === 2021) {
                return $version;
            }
        }
        throw new Exception('Version 2021 introuvable');
    }

    private function getVersion2027(mixed $departement): Version
    {
        foreach ($departement->getVersions() as $version) {
            if ($version->getAnnee() === 2027) {
                return $version;
            }
        }
        throw new Exception('Version 2021 introuvable');
    }
}
