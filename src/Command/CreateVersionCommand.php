<?php

namespace App\Command;

use App\Entity\Departement;
use App\Entity\Version;
use App\Repository\DepartementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-version',
    description: 'Create a new version for each department',
)]
class CreateVersionCommand extends Command
{
    public function __construct(
        private readonly DepartementRepository  $departementRepository,
        private readonly EntityManagerInterface $entityManager,
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
        /** @var Departement $departement */
        foreach ($departements as $departement) {
            $io->info("Traitement du département : ".$departement->getSigle());
            $version = $this->createVersion($departement);

            // récupérer tout ce qui est attaché au département pour l'attacher à la nouvelle version
            $apcParcours = $departement->getApcParcours();
            foreach ($apcParcours as $parcours) {
                $parcours->setVersion($version);
            }

            $apcCompetences = $departement->getApcCompetences();
            foreach ($apcCompetences as $competence) {
                $competence->setVersion($version);
            }

            $annees = $departement->getAnnees();
            foreach ($annees as $annee) {
                $annee->setVersion($version);
            }

            $qapes = $departement->getQapesSaes();
            foreach ($qapes as $qape) {
                $qape->setVersionDepartement($version);
            }

            $io->info("Fin Traitement du département : ".$departement->getSigle());
        }

        $this->entityManager->flush();


        return Command::SUCCESS;
    }

    private function createVersion(Departement $departement): Version
    {
        $version = new Version($departement);
        $version->setLibelle('Programme 2021');
        $version->setEtatPublication('Publié');
        $version->setAnnee(2021);
        $version->setActif(true);
        $version->setTextePresentation($departement->getTextePresentation());
        $version->setAltBut1($departement->getAltBut1());
        $version->setAltBut2($departement->getAltBut2());
        $version->setAltBut3($departement->getAltBut3());
        $version->setCoeffVerouille(!$departement->getCoeffEditable());
        $version->setDateVersionCompetence($departement->getDateVersionCompetence());
        $version->setDateVersionFormation($departement->getDateVersionFormation());
        $version->setPnVerouille($departement->getPnBloque());
        $version->setTextesVerouilles($departement->isVerouilleTextes());
        $version->setPnVerouille($departement->getPnBloque());
        $version->setVerouilleCompetences($departement->getVerouilleCompetences());
        $version->setVerouilleCroise($departement->getVerouilleCroise());
        $version->setVerouilleStructure($departement->getVerouilleStructure());
        $this->entityManager->persist($version);

        return $version;
    }
}
