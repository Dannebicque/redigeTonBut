<?php

namespace App\Classes\Apc;

use App\Classes\Word\MyWord;
use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use ZipArchive;

class ApcRessourcesExport
{
    protected ApcRessourceParcoursRepository $apcRessourceParcoursRepository;
    protected ApcRessourceRepository $apcRessourceRepository;
    protected MyWord $myWord;
    protected mixed $ressources;
    private string $dir;

    public function __construct(
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository,
        KernelInterface $kernel,
        MyWord $myWord
    ) {
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
        $this->apcRessourceRepository = $apcRessourceRepository;
        $this->myWord = $myWord;
        $this->dir = $kernel->getProjectDir() . '/public/upload/zip/';
    }


    public function export(Annee $annee, string $_format, ?ApcParcours $parcours)
    {
        if ($parcours !== null) {
            $this->ressources = $this->apcRessourceParcoursRepository->findByAnneeArray($annee, $parcours);
        } else {
            $this->ressources = $this->apcRessourceRepository->findByAnneeArray($annee);
        }

        switch ($_format) {
            case 'docx':
                return $this->exportZipWord();
                break;
            case 'pdf':
                break;

        }


    }

    private function exportZipWord()
    {
        $zip = new ZipArchive();
        $fileName = 'ressources-' . date('YmdHis') . '.zip';
        // The name of the Zip documents.
        $zipName = $this->dir . $fileName;

        $zip->open($zipName, ZipArchive::CREATE);
        $tabFiles = [];

        foreach ($this->ressources as $key => $ressources) {
            foreach ($ressources as $ressource) {
                $fichier = $this->myWord->exportAndSaveressource($ressource, $this->dir);
                $nomfichier = 'ressource_' . $ressource->getCodeMatiere() . '.docx';
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
