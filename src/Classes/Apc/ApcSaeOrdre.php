<?php
namespace App\Classes\Apc;

use App\Entity\ApcSae;
use App\Entity\Semestre;
use App\Repository\ApcSaeRepository;
use Doctrine\ORM\EntityManagerInterface;

class ApcSaeOrdre
{
    private EntityManagerInterface $entityManager;
    private ApcSaeRepository $apcSaeRepository;


    public function __construct(EntityManagerInterface $entityManager, ApcSaeRepository $apcSaeRepository)
    {
        $this->entityManager = $entityManager;
        $this->apcSaeRepository = $apcSaeRepository;
    }


    public function getOrdreSuivant(Semestre $semestre): int
    {
        //récupère l'ordre de la dernière ressource du semestre
        $ordreMax = $this->apcSaeRepository->findOrdreMax($semestre);

        //retourne +1
        return $ordreMax++;
    }

    public function deplaceSae(ApcSae $apcSae, int $position)
    {
        //modifie l'ordre de la ressource
        $ordreInitial = $apcSae->getOrdre();
        //récupère toutes les ressources à déplacer
        if ($position === -1) {
            //trouver la ressource avant pour inverser
            $sae = $this->apcSaeRepository->findOneBy(['ordre' => $ordreInitial  - 1,'semestre' => $apcSae->getSemestre()->getId()]);

            if ($sae !== null) {
                $apcSae->setOrdre($ordreInitial - 1);
                $sae->setOrdre($ordreInitial);
                $this->entityManager->flush();
                return true;
            }
            return false;
        }

        if ($position === 1) {
            //trouver la ressource après pour inverser
            $sae = $this->apcSaeRepository->findOneBy(['ordre' => $ordreInitial  + 1,'semestre' => $apcSae->getSemestre()->getId()]);

            if ($sae !== null) {
                $apcSae->setOrdre($ordreInitial + 1);
                $sae->setOrdre($ordreInitial);
                $this->entityManager->flush();
                return true;
            }
            return false;
        }

        return false; //mauvaise nouvelle position
    }
}
