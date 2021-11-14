<?php


namespace App\Event;


use App\Entity\ApcSae;

class SaeEvent
{
    public const UPDATE_CODIFICATION = 'update.codification.sae';

    protected ApcSae $apcSae;

    public function __construct(ApcSae $apcSae)
    {
        $this->apcSae = $apcSae;
    }

    public function getApcSae(): ApcSae
    {
        return $this->apcSae;
    }

    public function setApcSae(ApcSae $apcSae): void
    {
        $this->apcSae = $apcSae;
    }


}
