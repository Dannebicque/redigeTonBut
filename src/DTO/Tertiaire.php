<?php


namespace App\DTO;


class Tertiaire extends Caracteristique
{
    public const int NB_HEURES_TOTAL = 1800;

    public const int POURCENTAGE_TP_PROJET = 40;

    public const int NB_HEURES_TP = 360;

    public function heuresAdaptationLocale(): int
    {
        return self::NB_HEURES_TOTAL * self::POURCENTAGE_ADAPTATION / 100;
    }

    public function totalHeuresAvecProjet(): int
    {
        return self::NB_HEURES_TOTAL + self::NB_HEURES_PROJET;
    }
}
