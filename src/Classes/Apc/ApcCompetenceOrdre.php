<?php
namespace App\Classes\Apc;

use App\Entity\ApcCompetence;
use App\Repository\ApcComptenceRepository;
use Doctrine\ORM\EntityManagerInterface;

class ApcCompetenceOrdre
{
    private EntityManagerInterface $entityManager;
    private ApcComptenceRepository $apcComptenceRepository;


    public function __construct(EntityManagerInterface $entityManager, ApcComptenceRepository $apcComptenceRepository)
    {
        $this->entityManager = $entityManager;
        $this->apcComptenceRepository = $apcComptenceRepository;

    }

    public function deplaceCompetence(ApcCompetence $apcCompetence, string $ordreInitial)
    {
        //modifie l'ordre de la ressource
        $ordreDestination = $apcCompetence->getCouleur();

        //récupère toutes les ressources à déplacer
        return $this->inverse($ordreInitial, $ordreDestination, $apcCompetence);
    }

    private function inverse(?int $ordreInitial, ?int $ordreDestination, ApcCompetence $apcCompetence): bool
    {
        $ressource = $this->apcComptenceRepository->findOther(
            $ordreDestination,
            $apcCompetence
        );
        $apcCompetence->setCouleur($ordreDestination);


        if ($ressource !== null) {
            $ressource->setCouleur($ordreInitial);
        }

        $this->entityManager->flush();

        return true;
    }
}
