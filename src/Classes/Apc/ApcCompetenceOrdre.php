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

    public function deplaceCompetence(ApcCompetence $apcCompetence, int $position)
    {
        //modifie l'ordre de la ressource
        $ordreInitial = (int)substr($apcCompetence->getCouleur(),1,1);

        //récupère toutes les ressources à déplacer
        return $this->inverse($ordreInitial, $ordreInitial + $position, $apcCompetence);
    }

    private function inverse(?int $ordreInitial, ?int $ordreDestination, ApcCompetence $apcCompetence): bool
    {
        $ressource = $this->apcComptenceRepository->findOneBy([
            'couleur' => 'c'.$ordreDestination,
            'departement' => $apcCompetence->getDepartement()->getId()
        ]);
        $apcCompetence->setCouleur('c'.$ordreDestination);


        if ($ressource !== null) {
            $ressource->setCouleur('c'.$ordreInitial);
        }

        $this->entityManager->flush();

        return true;
    }
}
