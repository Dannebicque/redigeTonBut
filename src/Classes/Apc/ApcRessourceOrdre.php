<?php
namespace App\Classes\Apc;

use App\Entity\ApcRessource;
use App\Entity\Semestre;
use App\Repository\ApcRessourceRepository;
use Doctrine\ORM\EntityManagerInterface;

class ApcRessourceOrdre
{
    private EntityManagerInterface $entityManager;
    private ApcRessourceRepository $apcRessourceRepository;


    public function __construct(EntityManagerInterface $entityManager, ApcRessourceRepository $apcRessourceRepository)
    {
        $this->entityManager = $entityManager;
        $this->apcRessourceRepository = $apcRessourceRepository;
    }


    public function getOrdreSuivant(Semestre $semestre): int
    {
        //récupère l'ordre de la dernière ressource du semestre
        $ordreMax = $this->apcRessourceRepository->findOrdreMax($semestre);

        //retourne +1
        return $ordreMax[0]['ordreMax'] === null ? 1 : $ordreMax[0]['ordreMax']++;
    }

    public function deplaceRessource(ApcRessource $apcRessource, int $position)
    {
        //modifie l'ordre de la ressource
        $ordreInitial = $apcRessource->getOrdre();

        //récupère toutes les ressources à déplacer
        return $this->inverse($ordreInitial, $ordreInitial + $position, $apcRessource);
    }

    private function inverse(?int $ordreInitial, ?int $ordreDestination, ApcRessource $apcRessource): bool
    {
        $ressource = $this->apcRessourceRepository->findOneBy([
            'ordre' => $ordreDestination,
            'semestre' => $apcRessource->getSemestre()->getId()
        ]);
        $apcRessource->setOrdre($ordreDestination);

        if ($ressource !== null) {
            $ressource->setOrdre($ordreInitial);
        }

        $this->entityManager->flush();

        return true;
    }
}
