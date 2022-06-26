<?php

namespace App\Classes;

use App\Classes\Latex\GenereFileRessource;
use App\Classes\Latex\GenereFileSae;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class MyPdfLatex
{

    public function __construct(
        protected GenereFileRessource $genereFileRessource,
        protected GenereFileSae $genereFileSae,
        protected KernelInterface $kernel
    ) {
    }

    public function exportRessource(ApcRessource $ressource)
    {
        $output = $this->kernel->getProjectDir() . '/public/pdf/' . $ressource->getDepartement()->getNumeroAnnexe() . '/';
        $fichierLatex = $this->genereFileRessource->genereFile($ressource, $output);

        sleep(1);
        $name = 'PN-BUT-' . $ressource->getDepartement()->getSigle() . '-' . $ressource->getSlugName();
//        $text = shell_exec('php ' . $this->kernel->getProjectDir() . '/public/pdf/compileLatex.php ' . $fichierLatex . ' ' . $this->kernel->getProjectDir() . '/public/pdf/' . $ressource->getDepartement()->getNumeroAnnexe());
        chmod($fichierLatex, 0744);
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'app:compile-latex',
            // (optional) define the value of command arguments
            'arg1' => $ressource->getDepartement()->getNumeroAnnexe(),
            'arg2' => $fichierLatex,

        ]);

        // You can use NullOutput() if you don't need the output
        $outputBuffer = new BufferedOutput();
        $application->run($input, $outputBuffer);

        // return the output, don't use if you used NullOutput()
        $contentBuffer = $outputBuffer->fetch();

        sleep(3);
        $response = new Response(file_get_contents($output . $name . '.pdf'));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.pdf"');
        $response->headers->set('Content-length', filesize($output . $name . '.pdf'));

        return $response;
    }

    public function exportSae(ApcSae $sae)
    {
        $output = $this->kernel->getProjectDir() . '/public/pdf/' . $sae->getDepartement()->getNumeroAnnexe() . '/';
        $fichierLatex = $this->genereFileSae->genereFile($sae, $output);

        sleep(1);
        $name = 'PN-BUT-' . $sae->getDepartement()->getSigle() . '-' . $sae->getSlugName();
        chmod($fichierLatex, 0744);
        $text = shell_exec('/usr/bin/pdflatex/pdftex -output-directory /var/www/redigeTonBut/public/pdf/1/ ' . $fichierLatex);
//        $text = shell_exec('php ' . $this->kernel->getProjectDir() . '/public/pdf/compileLatex.php ' . $fichierLatex . ' ' . $this->kernel->getProjectDir() . '/public/pdf/' . $sae->getDepartement()->getNumeroAnnexe());
        sleep(3);
        $response = new Response(file_get_contents($output . $name . '.pdf'));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $name . '.pdf"');
        $response->headers->set('Content-length', filesize($output . $name . '.pdf'));

        return $response;
    }
}
