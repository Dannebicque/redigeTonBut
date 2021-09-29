<?php

namespace App\Controller;

use App\Classes\Apc\ApcRessourcesExport;
use App\Classes\Apc\ApcSaesExport;
use App\Classes\Apc\TableauExport;
use App\Entity\Annee;
use App\Entity\ApcParcours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController
{
    #[Route('/export', name: 'export_index')]
    public function index(): Response
    {
        return $this->render('export/index.html.twig', [
        ]);
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
