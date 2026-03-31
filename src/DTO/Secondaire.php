<?php


namespace App\DTO;


class Secondaire extends Caracteristique
{
    public const int NB_HEURES_TOTAL = 2000;

    public const int POURCENTAGE_TP_PROJET = 50;

    public const int NB_HEURES_TP = 700;

    public function heuresAdaptationLocale(): int
    {
        return self::NB_HEURES_TOTAL * self::POURCENTAGE_ADAPTATION / 100;
    }

    public function totalHeuresAvecProjet(): int
    {
        return self::NB_HEURES_TOTAL + self::NB_HEURES_PROJET;
    }
}
