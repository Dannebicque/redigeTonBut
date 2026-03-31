<?php

namespace App\Command;

//ALTER TABLE apc_competence ADD numero_identifiant INT NOT NULL;
use App\Classes\Export\DepartementExport;
use App\Repository\DepartementRepository;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:version:competence',
    description: 'Genere le fichier de version de la competence',
)]
class VersionCompetenceCommand extends Command
{
    private string $baseDir;

    public function __construct(
        protected DepartementExport $departementExport,
        protected DepartementRepository $departementRepository,
        KernelInterface $kernel,
    )
    {
        $this->baseDir = $kernel->getProjectDir() . '/public/version/';
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $specialites = $this->departementRepository->findAll();
        foreach ($specialites as $specialite) {
            $io->note(sprintf('Génération pour la spécialité %s', $specialite->getLibelle()));
            $tabJson = $this->departementExport->genereJson($specialite);
            $name = 'but-' . $specialite->getSigle().
                '-' . $specialite->getNumeroAnnexe().'-2025-v1';

            $filePath = $this->baseDir . $specialite->getNumeroAnnexe() . '/' . $name . '.json';
            if (!is_dir($this->baseDir . $specialite->getNumeroAnnexe())) {
                if (!mkdir($concurrentDirectory = $this->baseDir . $specialite->getNumeroAnnexe(), 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
            }

            file_put_contents($filePath, json_encode($tabJson));
            $io->success(sprintf('Fichier %s généré pour la spécialité %s.', $name, $specialite->getLibelle()));
        }

        $io->success('Fichiers générés avec succès.');

        return Command::SUCCESS;
    }
}
