<?php

namespace App\Command;

use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-portfolio',
    description: 'Ajout du texte de base pour le portfolio',
)]
class UpdatePortfolioCommand extends Command
{


    public function __construct(
        private ApcSaeRepository $apcSaeRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $saes = $this->apcSaeRepository->findBy(['portfolio' => true]);
        foreach ($saes as $sae) {
            if ($sae->getSemestre() !== null) {
                switch ($sae->getSemestre()->getOrdreLmd()) {
                    case 1:
                        $objectifs = 'Au semestre 1, la démarche portfolio consistera en un point étape intermédiaire qui permettra à l\'étudiant de se positionner, sans être évalué, dans le processus d\'acquisition du niveau 1 des compétences de la première année du B.U.T.';
                        $description = 'L\'équipe pédagogique devra accompagner l\'étudiant dans la compréhension et l\'appropriation effectives du référentiel de compétences et de ses éléments constitutifs tels que les composantes essentielles en tant qu\'elles constituent des critères qualité. Seront également exposées les différentes possibilités de démonstration et d\'évaluation de l\'acquisition du niveau des compétences ciblé en première année par la mobilisation notamment d\'éléments de preuve issus de toutes les SAÉ. L\'enjeu est de permettre à l\'étudiant d\'engager une démarche d\'auto-positionnement et d\'auto-évaluation.';
                        break;
                    case 2:
                        $objectifs = 'Au semestre 2, la démarche portfolio permettra d\'évaluer l\'étudiant dans son processus d\'acquisition du niveau 1 des compétences de la première année du B.U.T., et dans sa capacité en faire la démonstration par la mobilisation d\'éléments de preuve argumentés et sélectionnés. L\'étudiant devra donc engager une posture réflexive et de distanciation critique en cohérence avec le degré de complexité des niveaux de compétences ciblés, tout en s\'appuyant sur l\'ensemble des mises en situation proposées dans le cadre des SAÉ de première année.';
                        $description = 'Prenant n\'importe quelle forme, littérale, analogique ou numérique, la démarche portfolio pourra être menée dans le cadre d\'ateliers au cours desquels l\'étudiant retracera la trajectoire individuelle qui a été la sienne durant la première année du B.U.T. au prisme du référentiel de compétences tout en adoptant une posture propice à une analyse distanciée et intégrative de l\'ensemble des SAÉ.';
                        break;
                    case 3:
                        $objectifs = 'Au semestre 3, la démarche portfolio consistera en un point étape intermédiaire qui permettra à l\'étudiant de se positionner, sans être évalué, dans le processus d\'acquisition des niveaux de compétences de la seconde année du B.U.T. et relativement au parcours suivi.';
                        $description = 'L\'équipe pédagogique devra accompagner l\'étudiant dans la compréhension et l\'appropriation effectives du référentiel de compétences et de ses éléments constitutifs tels que les composantes essentielles en tant qu\'elles constituent des critères qualité. Seront également exposées les différentes possibilités de démonstration et d\'évaluation de l\'acquisition des niveaux de compétences ciblés en deuxième année par la mobilisation notamment d\'éléments de preuve issus de toutes les SAÉ. L\'enjeu est de permettre à l\'étudiant d\'engager une démarche d\'auto-positionnement et d\'auto-évaluation tout en intégrant la spécificité du parcours suivi.';
                        break;
                    case 4:
                        $objectifs = 'Au semestre 4, la démarche portfolio permettra d\'évaluer l\'étudiant dans son processus d\'acquisition des niveaux de compétences de la deuxième année du B.U.T., et dans sa capacité en faire la démonstration par la mobilisation d\'éléments de preuve argumentés et sélectionnés. L\'étudiant devra donc engager une posture réflexive et de distanciation critique en cohérence avec le parcours suivi et le degré de complexité des niveaux de compétences ciblés, tout en s\'appuyant sur l\'ensemble des mises en situation proposées dans le cadre des SAÉ de deuxième année.';
                        $description = 'Prenant n\'importe quelle forme, littérale, analogique ou numérique, la démarche portfolio pourra être menée dans le cadre d\'ateliers au cours desquels l\'étudiant retracera la trajectoire individuelle qui a été la sienne durant la seconde année du B.U.T. au prisme du référentiel de compétences et du parcours suivi, tout en adoptant une posture propice à une analyse distanciée et intégrative de l\'ensemble des SAÉ.';
                        break;
                    case 5:
                        $objectifs = 'Au semestre 5, la démarche portfolio consistera en un point étape intermédiaire qui permettra à l\'étudiant de se positionner, sans être évalué, dans le processus d\'acquisition des niveaux de compétences de la troisième année du B.U.T. et relativement au parcours suivi.';
                        $description = 'L\'équipe pédagogique devra accompagner l\'étudiant dans la compréhension et l\'appropriation effectives du référentiel de compétences et de ses éléments constitutifs tels que les composantes essentielles en tant qu\'elles constituent des critères qualité. Seront également exposées les différentes possibilités de démonstration et d\'évaluation de l\'acquisition des niveaux de compétences ciblés en troisième année par la mobilisation notamment d\'éléments de preuve issus de toutes les SAÉ. L\'enjeu est de permettre à l\'étudiant d\'engager une démarche d\'auto-positionnement et d\'auto-évaluation tout en intégrant la spécificité du parcours suivi.';
                        break;
                    case 6:
                        $objectifs = 'Au semestre 6, la démarche portfolio permettra d\'évaluer l\'étudiant dans son processus d\'acquisition des niveaux de compétences de la troisième année du B.U.T., et dans sa capacité en faire la démonstration par la mobilisation d\'éléments de preuve argumentés et sélectionnés. L\'étudiant devra donc engager une posture réflexive et de distanciation critique en cohérence avec le parcours suivi et le degré de complexité des niveaux de compétences ciblés, tout en s\'appuyant sur l\'ensemble des mises en situation proposées dans le cadre des SAÉ de troisième année.';
                        $description = 'Prenant n\'importe quelle forme, littérale, analogique ou numérique, la démarche portfolio pourra être menée dans le cadre d\'ateliers au cours desquels l\'étudiant retracera la trajectoire individuelle qui a été la sienne durant la troisième année du B.U.T. au prisme du référentiel de compétences et du parcours suivi, tout en adoptant une posture propice à une analyse distanciée et intégrative de l\'ensemble des SAÉ.';
                        break;
                }
                $sae->setDescription($description);
                $sae->setObjectifs($objectifs);
                $this->entityManager->flush();
                $io->success(sprintf('Portfolio %s mise à jour',
                    $sae->getCodeMatiere() . ' ' . $sae->getLibelle()));
            }
        }


        $io->success('Portfolio mis à jour.');

        return Command::SUCCESS;
    }
}
