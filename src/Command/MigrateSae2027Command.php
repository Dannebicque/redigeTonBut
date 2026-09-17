<?php

namespace App\Command;

use App\Entity\ApcSae;
use App\Repository\VersionRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-sae-2027',
    description: 'Migration des portfolios et SAÉ PAE pour l\'année 2027',
)]
class MigrateSae2027Command extends Command
{
    public function __construct(
        private readonly VersionRepository $versionRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $versions = $this->versionRepository->findBy(['annee' => 2027]);

        if (empty($versions)) {
            $io->warning('Aucune version trouvée pour l\'année 2027.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Traitement de %d versions pour l\'année 2027.', count($versions)));

        foreach ($versions as $version) {
            $io->section(sprintf('Version: %s (%s)', $version->getLibelle(), $version->getDepartement()?->getSigle()));

            // Il faut accéder aux SAÉ de la version.
            // La structure montre que Version a des Semestres, et Semestre a des ApcSaes.
            foreach ($version->getSemestres() as $semestre) {
                /** @var ApcSae $sae */
                foreach ($semestre->getApcSaes() as $sae) {
//                    $changed = false;
                    $oldCode = $sae->getCodeMatiere();
                    $libelleCourt = $sae->getLibelleCourt();
                    // 1. Portfolios existants -> SAÉ classiques + (a supprimer)
//                    if ($sae->getPortfolio() === true && !str_starts_with(mb_strtoupper((string)$libelleCourt), 'PAÉ')) {
//                        $sae->setPortfolio(false);
//                        $sae->setLibelle($sae->getLibelle() . ' (a supprimer)');
//                        $changed = true;
//                        $io->text(sprintf('  - Portfolio -> SAÉ: %s', $sae->getLibelle()));
//                    }

                    // 2. SAÉ commençant par PAE/PAÉ -> Portfolio
                    // On vérifie le libellé court ou le libellé complet ?
                    // L'utilisateur dit "SAE commencant par PAE", on va regarder les deux par sécurité.


//                    if (($libelleCourt && (str_starts_with(mb_strtoupper((string)$libelleCourt), 'PAE') || str_starts_with(mb_strtoupper((string)$libelleCourt), 'PAÉ')))) {
//
//                        if ($sae->getPortfolio() !== true) {
//                            $sae->setPortfolio(true);
//                            $changed = true;
//                            $io->text(sprintf('  - SAÉ -> Portfolio: %s', $libelleCourt));
//                        }
//                    }

                    // 3. Changement du code (toujours recalculer le code pour être sûr, même si pas de changement de type/libellé, si l'utilisateur le demande implicitement par "changer le code")
                    // Cependant l'énoncé dit "mettre les SAE commencant par PAE en portfolio + changer le code",
                    // ce qui suggère de le faire pour les éléments impactés.
                    // Mais par sécurité, on peut forcer le recalcul si on veut être exhaustif.
                    // Ici on reste sur les éléments impactés par la migration demandée.
//                    if ($changed) {
                        $newCode = Codification::codeSae($sae, $sae->getApcSaeParcours());
                        $sae->setCodeMatiere($newCode);
                        $io->text(sprintf('    Code mis à jour: %s -> %s', $oldCode, $newCode));
//                    }
                }
            }
        }

        $this->entityManager->flush();

        $io->success('Migration terminée pour l\'année 2027.');

        return Command::SUCCESS;
    }
}
