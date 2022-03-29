<?php

namespace App\Utils;

use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcCompetence;
use App\Entity\ApcComposanteEssentielle;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Entity\Semestre;

class Codification
{
    public static function codeRessource(ApcRessource $apcRessource, $parcours) : string
    {
        return 'R'.$apcRessource->getSemestre()?->getOrdreLmd().'.'.self::codeParcoursRessource($parcours).self::codeSurDeuxChiffres($apcRessource->getOrdre());
    }

    public static function codeComposanteEssentielle(ApcComposanteEssentielle $apcComposanteEssentielle) : string
    {
        return 'CE'.$apcComposanteEssentielle->getCompetence()?->getNumero().'.'.self::codeSurDeuxChiffres($apcComposanteEssentielle->getOrdre());
    }

    public static function codeApprentissageCritique(ApcApprentissageCritique $apcApprentissageCritique) : string
    {
        return 'AC'.$apcApprentissageCritique->getNiveau()?->getAnnee()?->getOrdre().$apcApprentissageCritique->getCompetence()?->getNumero().'.'.self::codeSurDeuxChiffres($apcApprentissageCritique->getOrdre()).self::codeParcoursAc($apcApprentissageCritique);
    }

    public static function codeUe(ApcCompetence $apcCompetence, Semestre $semestre) : string
    {
        // juste pour affichage
        return 'UE'.$semestre->getOrdreLmd().'.'.$apcCompetence->getNumero();
    }

    public static function codeSae(ApcSae $apcSae, $parcours) : string
    {
        if ($apcSae->getPortfolio() === true) {
            return 'PORTFOLIO';
        }

        if ($apcSae->getStage() === true) {
            $texte =  'STAGE'.self::codeParcoursSae($parcours);
            return substr($texte, 0, -1);
        }
        return 'SAÉ '.$apcSae->getSemestre()?->getOrdreLmd().'.'.self::codeParcoursSae($parcours).self::codeSurDeuxChiffres($apcSae->getOrdre());
    }

    private static function codeSurDeuxChiffres(?int $ordre)
    {
        if ($ordre < 10) {
            return '0'.$ordre;
        }

        return $ordre;
    }

    private static function codeParcoursAc(ApcApprentissageCritique $apcApprentissageCritique)
    {
        $nbParcoursAC = $apcApprentissageCritique->getNiveau()?->getApcParcoursNiveaux();
        $nbParcoursComp = $apcApprentissageCritique->getCompetence()->getDepartement()->getApcParcours();
        if ( $apcApprentissageCritique->getCompetence()->getDepartement()->getTypeStructure() === Departement::TYPE1)
        {
            if (count($nbParcoursComp) !== count($nbParcoursAC))
            {
                if (count($nbParcoursAC) === 1) {
                    return $nbParcoursAC[0]->getParcours()->getCode();
                }
            }
        }
    }

    private static function codeParcoursSae($parcours)
    {
        if (count($parcours) === 1 && $parcours !== null) {
            return $parcours[0]->getParcours()->getCode().'.';
        }
    }

    private static function codeParcoursRessource($parcours)
    {
        if (count($parcours) === 1 && $parcours !== null) {
            return $parcours[0]->getParcours()->getCode().'.';
        }
    }

}
