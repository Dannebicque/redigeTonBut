<?php

namespace App\Utils;

use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcCompetence;
use App\Entity\ApcComposanteEssentielle;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\Semestre;

class Codification
{
    public static function codeRessource(ApcRessource $apcRessource) : string
    {

        return $apcRessource->getSemestre()?->getOrdreLmd().'.'.self::codeSurDeuxChiffres($apcRessource->getOrdre());
    }

    public static function codeComposanteEssentielle(ApcComposanteEssentielle $apcComposanteEssentielle) : string
    {
        return $apcComposanteEssentielle->getCompetence()?->getNumero().'.'.self::codeSurDeuxChiffres($apcComposanteEssentielle->getOrdre());
    }

    public static function codeApprentissageCritique(ApcApprentissageCritique $apcApprentissageCritique) : string
    {
        return $apcApprentissageCritique->getNiveau()?->getAnnee()?->getOrdre().$apcApprentissageCritique->getCompetence()?->getNumero().'.'.self::codeSurDeuxChiffres($apcApprentissageCritique->getOrdre());
    }

    public static function codeUe(ApcCompetence $apcCompetence, Semestre $semestre) : string
    {
        // juste pour affichage
        return $semestre->getOrdreLmd().'.'.$apcCompetence->getNumero();
    }

    public static function codeSae(ApcSae $apcSae) : string
    {
        return $apcSae->getSemestre()?->getOrdreLmd().'.'.self::codeSurDeuxChiffres($apcSae->getOrdre());
    }

    private static function codeSurDeuxChiffres(?int $ordre)
    {
        if ($ordre < 10) {
            return '0'.$ordre;
        }

        return $ordre;
    }
}
