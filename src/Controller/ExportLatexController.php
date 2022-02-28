<?php

namespace App\Controller;

use App\Classes\Apc\ApcStructure;
use App\Classes\Latex\GenereFile;
use App\Classes\MyPdfLatex;
use App\Classes\Word\MyWord;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ExportLatexController extends BaseController
{
    private MyPdfLatex $myPdfLatex;

    public function __construct(
        MyPdfLatex $myPdfLatex
    )
    {
        $this->myPdfLatex = $myPdfLatex;
    }

    #[Route('/export/latex', name: 'export_latex')]
    public function index(
        ApcStructure $apcStructure,
        KernelInterface $kernel,
        Environment $environment
    ): Response {
//        $file = new GenereFile($apcStructure, $environment, $kernel->getProjectDir() . '/public/latex/', $this->getDepartement());
//        $fichierLatex = $file->genereFile();
//
//        $output = $kernel->getProjectDir() . '/public/pdf/';
//        $cle = new DateTime('now');
//        $name = 'PN-BUT-' . $this->getDepartement()->getSigle(); // . '-' . $cle->format('dmY-Hi');
//        exec('/Library/TeX/texbin/pdflatex -output-directory=' . $output . ' -jobname=' . $name . ' ' . $fichierLatex);
//
//        $response = new Response(file_get_contents($output . $name . '.pdf'));
//        $response->headers->set('Content-Type', 'application/pdf');
//        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.pdf"');
//        $response->headers->set('Content-length', filesize($output . $name . '.pdf'));
//
//        return $response;
    }

    #[Route('/export/pdf/ressource/{ressource}', name: 'export_pdf_ressource')]
    public function exportpdfRessource(ApcRessource $ressource): Response
    {
        return $this->myPdfLatex->exportRessource($ressource);
    }

    #[Route('/export/pdf/sae/{sae}', name: 'export_pdf_sae')]
    public function exportpdfSae(ApcSae $sae): Response
    {
        return $this->myPdfLatex->exportSae($sae);
    }
}
