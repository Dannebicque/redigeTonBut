<?php
namespace App\Classes\Apc;

use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcComposanteEssentielle;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcComposanteEssentielleRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;

class ApcComposanteEssentielleOrdre
{
    private EntityManagerInterface $entityManager;
    private Codification $codification;
    private ApcComposanteEssentielleRepository $apcComposanteEssentielleRepository;


    public function __construct(Codification $codification, EntityManagerInterface $entityManager, ApcComposanteEssentielleRepository $apcComposanteEssentielleRepository)
    {
        $this->entityManager = $entityManager;
        $this->codification = $codification;
        $this->apcComposanteEssentielleRepository = $apcComposanteEssentielleRepository;
    }

    public function deplaceApcComposanteEssentielle(ApcComposanteEssentielle $apcComposanteEssentielle, int $ordreInitial)
    {
        //modifie l'ordre de la ressource
        $ordreFinal = $apcComposanteEssentielle->getOrdre();

        //récupère toutes les ressources à déplacer
        return $this->inverse($ordreInitial, $ordreFinal, $apcComposanteEssentielle);
    }

    private function inverse(?int $ordreInitial, ?int $ordreDestination, ApcComposanteEssentielle $apcComposanteEssentielle): bool
    {
        $ressource = $this->apcComposanteEssentielleRepository->findOther(
            $ordreDestination,
            $apcComposanteEssentielle
        );

        $apcComposanteEssentielle->setOrdre($ordreDestination);
        $apcComposanteEssentielle->setCode($this->codification::codeComposanteEssentielle($apcComposanteEssentielle));

        if ($ressource !== null) {
            $ressource->setOrdre($ordreInitial);
            $ressource->setCode($this->codification::codeComposanteEssentielle($ressource));
        }

        $this->entityManager->flush();

        return true;
    }
}
