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
    public static function codeRessource(ApcRessource $apcRessource) : string
    {

        return 'R'.$apcRessource->getSemestre()?->getOrdreLmd().'.'.self::codeSurDeuxChiffres($apcRessource->getOrdre()).self::codeParcoursRessource($apcRessource);
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

    public static function codeSae(ApcSae $apcSae) : string
    {
        if ($apcSae->getPortfolio() === true) {
            return 'SAE'.$apcSae->getSemestre()?->getOrdreLmd().'.PORTFOLIO';
        }

        if ($apcSae->getStage() === true) {
            return 'SAE'.$apcSae->getSemestre()?->getOrdreLmd().'.STAGE';
        }
        return 'SAE'.$apcSae->getSemestre()?->getOrdreLmd().'.'.self::codeSurDeuxChiffres($apcSae->getOrdre()).self::codeParcoursSae($apcSae);
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

    private static function codeParcoursSae(ApcSae $apcSae)
    {
        $nbParcours = $apcSae->getApcSaeParcours();
        if (count($nbParcours) === 1 && $nbParcours !== null) {
            return $apcSae->getApcSaeParcours()[0]->getParcours()->getCode();
        }
    }

    private static function codeParcoursRessource(ApcRessource $apcRessource)
    {
        $nbParcours = $apcRessource->getApcRessourceParcours();
        if (count($nbParcours) === 1 && $nbParcours !== null) {
            return $apcRessource->getApcRessourceParcours()[0]->getParcours()->getCode();
        }
    }

}
