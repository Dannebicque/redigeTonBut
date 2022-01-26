<?php

namespace App\Command;

use App\Repository\ApcRessourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-ppp',
    description: 'Ajout du texte de base pour le PPP',
)]
class UpdatePppCommand extends Command
{


    public function __construct(
        private ApcRessourceRepository $apcRessourceRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $ressources = $this->apcRessourceRepository->findPPP();
        foreach ($ressources as $ressource) {
            if ($ressource->getSemestre() !== null) {
                switch ($ressource->getSemestre()->getOrdreLmd()) {
                    case 1:
                    case 2:
                        $ppp = '- S’approprier la démarche PPP : connaissance de soi (intérêt, curiosité, aspirations, motivations), accompagner les étudiants dans la définition d’une stratégie personnelle permettant la réalisation du projet professionnel' . "\r\n" .
                            '- S\'approprier la formation' . "\r\n" .
                            '- Découvrir les métiers et connaître le territoire' . "\r\n" .
                            '- Se projeter dans un environnement professionnel';
                        break;
                    case 3:
                    case 4:
                        $ppp = '- Définir son profil, en partant de ses appétences, de ses envies et asseoir son choix professionnel notamment au travers de son parcours.' . "\r\n" .
                            '- Construire un/des projet(s) professionnel(s) en définissant une stratégie personnelle pour le/les réaliser' . "\r\n" .
                            '- Analyser les métiers envisagés : postes, types d\'organisation, secteur, environnement professionnel.' . "\r\n" .
                            '- Mettre en place une démarche de recherche de stage et d\'alternance et les outils associés';
                        break;
                    case 5:
                    case 6:
                        $ppp = '- Connaissance de soi et posture professionnelle (en lien avec années 1&2)' . "\r\n" .
                            '- Formaliser son plan de carrière' . "\r\n" .
                            '- S\'approprier le processus et s\'adapter aux différents types de recrutement';
                        break;
                }
                $description = $ressource->getDescription();
                $ressource->setDescription($ppp . "\r\n \r\n". $description);
                $io->text($ressource->getDescription());
                //$this->entityManager->flush();
                $io->success(sprintf('Ressource %s mise à jour',
                    $ressource->getCodeMatiere() . ' ' . $ressource->getLibelle()));
            }
        }


        $io->success('PPP mis à jour.');

        return Command::SUCCESS;
    }
}
