<?php

namespace App\Controller;

use App\Classes\Apc\ApcReferentielFormationExport;
use App\Classes\Apc\ApcRessourcesExport;
use App\Classes\Apc\ApcSaesExport;
use App\Classes\Apc\TableauExport;
use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\Version;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExportController extends BaseController
{
    #[Route('/export', name: 'export_index')]
    public function index(): Response
    {
        return $this->render('export/index.html.twig', [
        ]);
    }

    #[Route('/export-referentiel-formation/excel', name: 'export_referentiel_format_excel')]
    public function exportReferentielFormationExcel(
        ApcReferentielFormationExport $apcReferentielFormationExport): Response
    {
        return $apcReferentielFormationExport->export($this->getVersion(), 'xlsx');
    }

    #[Route('/export-synthese-referentiel-formation/excel', name: 'export_referentiel_synthese_format_excel')]
    public function exportReferentielSyntheseFormationExcel(
        ApcReferentielFormationExport $apcReferentielFormationExport): Response
    {
        return $apcReferentielFormationExport->exportSynthese($this->getVersion());
    }

    #[Route('/export-synthese-referentiel-formation/acd/excel', name: 'export_referentiel_synthese_format_excel_acd')]
    public function exportReferentielSyntheseFormationAcdExcel(
        ApcReferentielFormationExport $apcReferentielFormationExport): Response
    {
        return $apcReferentielFormationExport->exportSyntheseAcd($this->getVersion());
    }


    #[Route('/export-referentiel-formation/word', name: 'export_referentiel_format_word')]
    public function exportReferentielFormationWord(
        ApcReferentielFormationExport $apcReferentielFormationExport): Response
    {
        return $apcReferentielFormationExport->export($this->getVersion(), 'docx');
    }

    #[Route('/export-referentiel-formation/word-al', name: 'export_referentiel_format_word_al')]
    public function exportReferentielFormationWordAl(
        ApcReferentielFormationExport $apcReferentielFormationExport): Response
    {
        return $apcReferentielFormationExport->export($this->getVersion(), 'al');
    }

    #[Route('/export-ressources/{annee}.{_format}/{parcours}', name: 'export_ressources_annee')]
    public function exportRessources(
        ApcRessourcesExport $apcRessourcesExport,
        Annee $annee, string $_format,
        ?ApcParcours $parcours = null): Response
    {
        return $apcRessourcesExport->export($annee, $_format, $parcours);
    }

    #[Route('/export-saes/{annee}.{_format}/{parcours}', name: 'export_saes_annee')]
    public function exportSaes(ApcSaesExport $apcSaesExport, Annee $annee, string $_format, ?ApcParcours $parcours = null): Response
    {
        return $apcSaesExport->export($annee, $_format, $parcours);
    }

    #[Route('/export-tableau-croise/{annee}/{parcours}', name: 'export_tableau_croise_annee')]
    public function exportTableauCroise(
        TableauExport $tableauExport,
        Annee $annee, ?ApcParcours $parcours = null): Response
    {
        return $tableauExport->exportTableauCroise($annee, $parcours);
    }

    #[Route('/export-tableau-croise-volumes-horaires/{version}', name: 'export_tableau_croise_et_volume_horaire')]
    public function exportTableauCroiseVolumesHoraires(
        TableauExport $tableauExport,
        Version $version): Response
    {
        return $tableauExport->exportTableauCroiseVolumeHoraire($version);
    }

    #[Route('/export-tableau-horaire/{annee}/{parcours}', name: 'export_tableau_horaire_annee')]
    public function exportTableauHoraire(TableauExport $tableauExport,Annee $annee, ?ApcParcours $parcours = null): Response
    {
        return $tableauExport->exportTableauHoraire($annee, $parcours);
    }

    #[Route('/export-tableau-preconisation/{annee}/{parcours}', name: 'export_tableau_preconisations_annee')]
    public function exportTableauPreconisation(TableauExport $tableauExport,Annee $annee, ?ApcParcours $parcours = null): Response
    {
        return $tableauExport->exportTableauPreconisation($annee, $parcours);
    }
}
