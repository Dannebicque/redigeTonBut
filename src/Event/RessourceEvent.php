<?php


namespace App\Event;


use App\Entity\ApcRessource;

class RessourceEvent
{
    public const UPDATE_CODIFICATION = 'update.codification.ressource';

    protected ApcRessource $apcRessource;

    public function __construct(ApcRessource $apcRessource)
    {
        $this->apcRessource = $apcRessource;
    }

    public function getApcRessource(): ApcRessource
    {
        return $this->apcRessource;
    }

    public function setApcRessource(ApcRessource $apcRessource): void
    {
        $this->apcRessource = $apcRessource;
    }


}
