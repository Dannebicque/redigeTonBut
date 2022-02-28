<?php

namespace App\Classes;

use App\Classes\Latex\GenereFile;
use App\Classes\Latex\GenereFileRessource;
use App\Classes\Latex\GenereFileSae;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class MyPdfLatex
{

    public function __construct(
        protected GenereFileRessource $genereFileRessource,
        protected GenereFileSae $genereFileSae,
        protected KernelInterface $kernel
    ) {}

    public function exportRessource(ApcRessource $ressource)
    {
        $output = $this->kernel->getProjectDir() . '/public/pdf/'.$ressource->getDepartement()->getNumeroAnnexe().'/';
        $fichierLatex = $this->genereFileRessource->genereFile($ressource, $output);

        sleep(3);
        $name = 'PN-BUT-' . $ressource->getDepartement()->getSigle().'-'.$ressource->getSlugName();
        shell_exec('pdflatex -output-directory=' . $output . ' -jobname=' . $name . ' ' . $fichierLatex);

        $response = new Response(file_get_contents($output . $name . '.pdf'));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.pdf"');
        $response->headers->set('Content-length', filesize($output . $name . '.pdf'));

        return $response;
    }

    public function exportSae(ApcSae $sae)
    {
        $output = $this->kernel->getProjectDir() . '/public/pdf/'.$sae->getDepartement()->getNumeroAnnexe().'/';
        $fichierLatex = $this->genereFileSae->genereFile($sae, $output);

        sleep(3);
        $name = 'PN-BUT-' . $sae->getDepartement()->getSigle().'-'.$sae->getSlugName();
        shell_exec('pdflatex -output-directory=' . $output . ' -jobname=' . $name . ' ' . $fichierLatex);

        $response = new Response(file_get_contents($output . $name . '.pdf'));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.pdf"');
        $response->headers->set('Content-length', filesize($output . $name . '.pdf'));

        return $response;
    }
}
