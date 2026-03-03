<?php

namespace App\Utils;

use App\Entity\Departement;

class Files
{
    private string $projectDir = '';

    public function __construct(string $kernelProjectDir)
    {
        $this->projectDir = $kernelProjectDir;
    }

    public function getVersion2022(Departement $departement): ?string
    {
// récupérer le fichier le plus récent dans le dossier associé au numéro d'annexe du département
        $directory = $this->projectDir . '/public/version/' . $departement->getNumeroAnnexe();

        $latestFile = 'but-'.$departement->getSigle().'-'.$departement->getNumeroAnnexe().'-2022.json';

        if ($latestFile) {
            return $directory . '/' . $latestFile;

        }
        return null;
    }


    public function getLastVersionFile(Departement $departement): ?string
    {
// récupérer le fichier le plus récent dans le dossier associé au numéro d'annexe du département
        $directory = $this->projectDir . '/public/version/' . $departement->getNumeroAnnexe();
        $files = scandir($directory);
        $latestFile = null;
        $latestTime = 0;
        foreach ($files as $file) {
            if (is_file($directory . '/' . $file)) {
                $fileTime = filemtime($directory . '/' . $file);
                if ($fileTime > $latestTime) {
                    $latestTime = $fileTime;
                    $latestFile = $file;
                }
            }
        }

        if ($latestFile) {
            return $directory . '/' . $latestFile;

        }
        return null;
    }

    public function getLastVersionReferentielFile(Departement $departement)
    {
        $directory = $this->projectDir . '/public/version-referentiel/' . $departement->getNumeroAnnexe();
        $files = scandir($directory);
        $latestFile = null;
        $latestTime = 0;
        foreach ($files as $file) {
            if (is_file($directory . '/' . $file)) {
                $fileTime = filemtime($directory . '/' . $file);
                if ($fileTime > $latestTime) {
                    $latestTime = $fileTime;
                    $latestFile = $file;
                }
            }
        }

        if ($latestFile) {
            return $directory . '/' . $latestFile;

        }
        return null;
    }
}
