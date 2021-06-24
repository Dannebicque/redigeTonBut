<?php


namespace App\DTO;


class Secondaire extends Caracteristique
{
    public const NB_HEURES_TOTAL = 2000;
    public const POURCENTAGE_TP_PROJET = 50;

    public function heuresAdaptationLocale()
    {
        return self::NB_HEURES_TOTAL * self::POURCENTAGE_ADAPTATION / 100;
    }

    public function totalHeuresAvecProjet()
    {
        return self::NB_HEURES_TOTAL + self::NB_HEURES_PROJET;
    }
}
