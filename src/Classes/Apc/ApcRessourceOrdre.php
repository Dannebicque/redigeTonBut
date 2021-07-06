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
        return $ordreMax++;
    }

    public function deplaceRessource(ApcRessource $apcRessource, int $position)
    {
        //modifie l'ordre de la ressource
        $ordreInitial = $apcRessource->getOrdre();
        //récupère toutes les ressources à déplacer
        if ($position === -1) {
            //trouver la ressource avant pour inverser
            $ressource = $this->apcRessourceRepository->findOneBy(['ordre' => $ordreInitial  - 1,'semestre' => $apcRessource->getSemestre()->getId()]);

            if ($ressource !== null) {
                $apcRessource->setOrdre($ordreInitial - 1);
                $ressource->setOrdre($ordreInitial);
                $this->entityManager->flush();
                return true;
            }
            return false;
        }

        if ($position === 1) {
            //trouver la ressource après pour inverser
            $ressource = $this->apcRessourceRepository->findOneBy(['ordre' => $ordreInitial  + 1,'semestre' => $apcRessource->getSemestre()->getId()]);

            if ($ressource !== null) {
                $apcRessource->setOrdre($ordreInitial + 1);
                $ressource->setOrdre($ordreInitial);
                $this->entityManager->flush();
                return true;
            }
            return false;
        }

        return false; //mauvaise nouvelle position
    }
}
