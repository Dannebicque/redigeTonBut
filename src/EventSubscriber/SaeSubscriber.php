<?php

namespace App\EventSubscriber;

use App\Event\SaeEvent;
use App\Repository\ApcSaeParcoursRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SaeSubscriber implements EventSubscriberInterface
{
    protected EntityManagerInterface $entityManager;
    protected ApcSaeParcoursRepository $apcSaeRepository;

    public function __construct(EntityManagerInterface $entityManager,ApcSaeParcoursRepository $apcSaeRepository)
    {
        $this->entityManager = $entityManager;
        $this->apcSaeRepository = $apcSaeRepository;
    }


    public static function getSubscribedEvents()
    {
        return [
            SaeEvent::UPDATE_CODIFICATION => 'onUpdateCodification',
        ];
    }

    public function onUpdateCodification(SaeEvent $saeEvent)
    {
        $sae = $saeEvent->getApcSae();
        $parcours = $this->apcSaeRepository->findSaeWithParcours($sae->getId());
        if ($sae !== null) {
            $sae->setCodeMatiere(Codification::codeSae($sae, $parcours));
            $this->entityManager->flush();
        }
    }


}
