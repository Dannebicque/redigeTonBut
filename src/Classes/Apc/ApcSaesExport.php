<?php

namespace App\Classes\Apc;

use App\Classes\Word\MyWord;
use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use ZipArchive;

class ApcSaesExport
{
    protected ApcSaeParcoursRepository $apcSaeParcoursRepository;
    protected ApcSaeRepository $apcSaeRepository;
    protected MyWord $myWord;
    protected mixed $saes;
    private string $dir;

    public function __construct(
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcSaeRepository $apcSaeRepository,
        KernelInterface $kernel,
        MyWord $myWord
    ) {
        $this->apcSaeParcoursRepository = $apcSaeParcoursRepository;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->myWord = $myWord;
        $this->dir = $kernel->getProjectDir() . '/public/upload/zip/';
    }


    public function export(Annee $annee, string $_format, ?ApcParcours $parcours)
    {
        if ($parcours !== null) {
            $this->saes = $this->apcSaeParcoursRepository->findByAnneeArray($annee, $parcours);
        } else {
            $this->saes = $this->apcSaeRepository->findByAnneeArray($annee);
        }

        switch ($_format) {
            case 'docx':
                return $this->exportZipWord();
            case 'pdf':
                break;

        }


    }

    private function exportZipWord()
    {
        $zip = new ZipArchive();
        $fileName = 'saes-' . date('YmdHis') . '.zip';
        // The name of the Zip documents.
        $zipName = $this->dir . $fileName;

        $zip->open($zipName, ZipArchive::CREATE);
        $tabFiles = [];

        foreach ($this->saes as $key => $saes) {
            foreach ($saes as $sae) {
                $fichier = $this->myWord->exportAndSaveSae($sae, $this->dir);
                $nomfichier = 'sae_' . $sae->getCodeMatiere() . '.docx';
                $tabFiles[] = $fichier;
                $zip->addFile($fichier, $nomfichier);
            }
        }

        $zip->close();

        foreach ($tabFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }


        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Content-length', filesize($zipName));

        return $response;
    }
}
