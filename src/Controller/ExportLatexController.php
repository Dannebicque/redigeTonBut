<?php

namespace App\Controller;

use App\Classes\Apc\ApcStructure;
use App\Classes\Latex\GenereFile;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ExportLatexController extends BaseController
{
    #[Route('/export/latex', name: 'export_latex')]
    public function index(
        ApcStructure $apcStructure,
        KernelInterface $kernel,
        Environment $environment
    ): Response {
        $file = new GenereFile($apcStructure, $environment, $kernel->getProjectDir() . '/public/latex/', $this->getDepartement());
        $fichierLatex = $file->genereFile();

        $output = $kernel->getProjectDir() . '/public/pdf/';
        $cle = new DateTime('now');
        $name = 'PN-BUT-' . $this->getDepartement()->getSigle(); // . '-' . $cle->format('dmY-Hi');
        exec('/Library/TeX/texbin/pdflatex -output-directory=' . $output . ' -jobname=' . $name . ' ' . $fichierLatex);

        $response = new Response(file_get_contents($output . $name . '.pdf'));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.pdf"');
        $response->headers->set('Content-length', filesize($output . $name . '.pdf'));

        return $response;
    }
}
