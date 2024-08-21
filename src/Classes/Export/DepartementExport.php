<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Classes/Structure/DiplomeExport.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 31/05/2021 20:35
 */

namespace App\Classes\Export;

use App\Entity\Departement;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DepartementExport
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function exportRefentiel(Departement $departement): Response
    {
        $xmlContent = $this->twig->render('xml/export-referentiel-but.xml.twig', [
            'departement' => $departement,
            'competences' => $departement->getApcCompetences(),
            'parcours' => $departement->getApcParcours(),
        ]);
        $name = 'but-' . $departement->getSigle();


        return $this->exportFichier($xmlContent, $name);
    }

    public function exportFichier(string $xmlContent, string $name): Response
    {
        $date = new DateTime('now');
        $name .= '-'.$date->format('dmY-His');
        $response = new Response($xmlContent);
        $response->headers->set('Content-type', 'text/xml');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.xml"');

        return $response;
    }

    public function exportProgramme(Departement $departement): Response
    {
        $xmlContent = $this->twig->render('xml/export-programme-but.xml.twig', [
            'semestres' => $departement->getSemestres(),
        ]);
        $name = 'but-pn-' . $departement->getSigle();


        return $this->exportFichier($xmlContent, $name);
    }

    public function exportAllRefentiel()
    {
    }
}
