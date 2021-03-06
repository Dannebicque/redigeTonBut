<?php
namespace App\Classes\Apc;

use App\Entity\ApcApprentissageCritique;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;

class ApcApprentissageCritiqueOrdre
{
    private EntityManagerInterface $entityManager;
    private Codification $codification;
    private ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository;


    public function __construct(Codification $codification, EntityManagerInterface $entityManager, ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository)
    {
        $this->entityManager = $entityManager;
        $this->codification = $codification;
        $this->apcApprentissageCritiqueRepository = $apcApprentissageCritiqueRepository;
    }

    public function deplaceApprentissageCritique(ApcApprentissageCritique $apcApprentissageCritique, int $ordreInitial)
    {
        //modifie l'ordre de la ressource
        $ordreFinal = $apcApprentissageCritique->getOrdre();

        //récupère toutes les ressources à déplacer
        return $this->inverse($ordreInitial, $ordreFinal, $apcApprentissageCritique);
    }

    private function inverse(?int $ordreInitial, ?int $ordreDestination, ApcApprentissageCritique $apcApprentissageCritique): bool
    {
        $ressource = $this->apcApprentissageCritiqueRepository->findOther(
            $ordreDestination,
            $apcApprentissageCritique
        );

        $apcApprentissageCritique->setOrdre($ordreDestination);
        $apcApprentissageCritique->setCode($this->codification::codeApprentissageCritique($apcApprentissageCritique));

        if ($ressource !== null) {
            $ressource->setOrdre($ordreInitial);
            $ressource->setCode($this->codification::codeApprentissageCritique($ressource));
        }

        $this->entityManager->flush();

        return true;
    }

    public function deplaceApprentissageCritiquePosition(
        ApcApprentissageCritique $apcApprentissageCritique,
        int $position
    ) {
        //modifie l'ordre de la ressource
        $ordreInitial = $apcApprentissageCritique->getOrdre();

        //récupère toutes les ressources à déplacer
        return $this->inverse($ordreInitial, $ordreInitial + $position, $apcApprentissageCritique);
    }
}
