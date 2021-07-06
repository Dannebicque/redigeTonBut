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
        return $this->inverse($ordreInitial, $ordreInitial + $position, $apcSae);
    }

    private function inverse(?int $ordreInitial, ?int $ordreDestination, ApcSae $apcSae): bool
    {
        $sae = $this->apcSaeRepository->findOneBy([
            'ordre' => $ordreDestination,
            'semestre' => $apcSae->getSemestre()->getId()
        ]);
        $apcSae->setOrdre($ordreDestination);

        if ($sae !== null) {
            $sae->setOrdre($ordreInitial);
        }

        $this->entityManager->flush();

        return true;
    }
}
