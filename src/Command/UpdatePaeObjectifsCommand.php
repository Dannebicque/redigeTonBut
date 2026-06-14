<?php

namespace App\Command;

use App\Repository\ApcSaeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-pae-objectifs',
    description: 'Met a jour les objectifs des SAE PAE de S1, S3 et S5.',
)]
class UpdatePaeObjectifsCommand extends Command
{
    private const string OBJECTIFS_S1 = <<<'TEXT'
Au semestre 1, l’objectif premier est l’acquisition de la démarche PAÉ. Cette démarche conduit à une première production concernant a minima une compétence. Cette production permet à l’étudiant de se positionner sans évaluation sommative sur le niveau de compétence acquis. L’enjeu est de permettre à l’étudiant d’engager une démarche d’auto-positionnement et d’auto-évaluation. Le PAÉ produit permet à l’équipe pédagogique de vérifier l’appropriation de la démarche PAÉ.
TEXT;

    private const string OBJECTIFS_S35 = <<<'TEXT'
Aux semestres impairs, la demarche conduit a la production d'un PAE intermediaire. Il permet a l'etudiant de se positionner, grace a une evaluation formative, au regard du niveau de competences attendu de son annee du B.U.T. et relativement au parcours suivi.

L'acquisition de cette demarche PAE pour les etudiants en passerelle entrante est indispensable.
TEXT;

    public function __construct(
        private readonly ApcSaeRepository $apcSaeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $saesPae = $this->apcSaeRepository->findPaeBySemestres([1, 3, 5]);
        if ($saesPae === []) {
            $io->warning('Aucune SAE PAE trouvee pour S1, S3 ou S5.');

            return Command::SUCCESS;
        }

        $updatedS1 = 0;
        $updatedS35 = 0;

        foreach ($saesPae as $sae) {
            $ordreLmd = $sae->getSemestre()?->getOrdreLmd();
            if ($ordreLmd === 1) {
                if ($sae->getObjectifs() !== self::OBJECTIFS_S1) {
                    $sae->setObjectifs(self::OBJECTIFS_S1);
                    ++$updatedS1;
                }
                continue;
            }

            if ($ordreLmd === 3 || $ordreLmd === 5) {
                if ($sae->getObjectifs() !== self::OBJECTIFS_S35) {
                    $sae->setObjectifs(self::OBJECTIFS_S35);
                    ++$updatedS35;
                }
            }
        }

        $this->entityManager->flush();

        $io->table(
            ['Cible', 'Total MAJ'],
            [
                ['PAE S1 -> objectifs', (string) $updatedS1],
                ['PAE S3/S5 -> objectifs', (string) $updatedS35],
            ]
        );
        $io->success('Mise a jour des objectifs PAE terminee.');

        return Command::SUCCESS;
    }
}

