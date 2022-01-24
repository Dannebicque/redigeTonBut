<?php

namespace App\Controller;

use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use DateTime;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportPdfController extends BaseController
{
    #[Route('/export/pdf/{parcours}', name: 'export_pdf')]
    public function index(Pdf $knpSnappyPdf,
        ApcSaeRepository $apcSaeRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository,
        ApcParcours $parcours): Response
    {
        $semestres = $this->getDepartement()->getSemestres();

        $ressources = [];
        $saes = [];

        foreach ($semestres as $semestre) {
            if ($this->getDepartement()->getTypeStructure() !== Departement::TYPE3 && $semestre->getOrdreLmd() < 3) {
                $ressources[$semestre->getId()] = $apcRessourceRepository->findBySemestre($semestre);
                $saes[$semestre->getId()] = $apcSaeRepository->findBySemestre($semestre);
            } else {
                $ressources[$semestre->getId()] = $apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
                $saes[$semestre->getId()] = $apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
            }
        }

        $day = new DateTime('now');
        $name = 'referentiel-formation-' . $this->getDepartement()->getSigle() . '_'.$parcours->getCode().'_'.$day->format('dmYHis').'.pdf';
        $html = $this->renderView('formation/export-referentiel.html.twig', [
            'allParcours' => $this->getDepartement()->getApcParcours(),
            'departement' => $this->getDepartement(),
            'semestres' => $semestres,
            'saes' => $saes,
            'ressources' => $ressources,
            'parcours' => $parcours,
        ]);

        $header = $this->renderView( 'export_pdf/_header.html.twig', ['sigle' => $this->getDepartement()->getSigle(), 'parcours' => $parcours->getLibelle()]);
        $footer = $this->renderView( 'export_pdf/_footer.html.twig' );

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html,
                [
                    'header-html' => $header,
//                    'footer-html' => $footer,
            ]
            ),
            $name
        );
    }
}
