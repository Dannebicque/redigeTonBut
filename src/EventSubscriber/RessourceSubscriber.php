<?php

namespace App\EventSubscriber;

use App\Event\RessourceEvent;
use App\Repository\ApcRessourceParcoursRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RessourceSubscriber implements EventSubscriberInterface
{
    protected EntityManagerInterface $entityManager;
    protected ApcRessourceParcoursRepository $apcRessourceParcoursRepository;

    public function __construct(EntityManagerInterface $entityManager,ApcRessourceParcoursRepository $apcRessourceParcoursRepository)
    {
        $this->entityManager = $entityManager;
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
    }


    public static function getSubscribedEvents()
    {
        return [
            RessourceEvent::UPDATE_CODIFICATION => 'onUpdateCodification',
        ];
    }

    public function onUpdateCodification(RessourceEvent $ressourceEvent)
    {
        $ressource = $ressourceEvent->getApcRessource();
        $parcours = $this->apcRessourceParcoursRepository->findSaeWithParcours($ressourceEvent->getApcRessource()->getId());
        if ($ressource !== null) {
            $ressource->setCodeMatiere(Codification::codeRessource($ressource, $parcours));
            $this->entityManager->flush();
        }
    }
}
