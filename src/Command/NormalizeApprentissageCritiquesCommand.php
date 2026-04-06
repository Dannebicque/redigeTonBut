<?php

namespace App\Command;

use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcComptenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:normalize-apprentissages-critiques',
    description: 'Normalise les libelles des apprentissages critiques (Unicode NFC).',
)]
class NormalizeApprentissageCritiquesCommand extends Command
{
    public function __construct(
        private readonly ApcComptenceRepository $apprentissageCritiqueRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Affiche les changements sans les sauvegarder.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        if (!class_exists(\Normalizer::class)) {
            $io->error('La classe Normalizer est indisponible. Activez ext-intl ou le polyfill intl-normalizer.');

            return Command::FAILURE;
        }

        $apprentissages = $this->apprentissageCritiqueRepository->findAll();
        $nbScannes = 0;
        $nbModifies = 0;

        foreach ($apprentissages as $apprentissage) {
            ++$nbScannes;
            $libelle = $apprentissage->getLibelle();

            if ($libelle === null || $libelle === '') {
                continue;
            }

            $libelleNormalise = \Normalizer::normalize($libelle, \Normalizer::FORM_C);
            if ($libelleNormalise === false || $libelleNormalise === $libelle) {
                continue;
            }

            ++$nbModifies;
            if ($dryRun) {
                $io->text(sprintf('[DRY-RUN] AC #%d (%s) sera normalisé par : %s', $apprentissage->getId(), $apprentissage->getCode(), $libelleNormalise));
                continue;
            }

            $apprentissage->setLibelle($libelleNormalise);
        }

        if ($dryRun) {
            $io->success(sprintf('%d apprentissages scannes, %d changements detectes (non sauvegardes).', $nbScannes, $nbModifies));

            return Command::SUCCESS;
        }

        if ($nbModifies > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('%d apprentissages scannes, %d libelles normalises.', $nbScannes, $nbModifies));

        return Command::SUCCESS;
    }
}

