<?php

namespace App\Controller\api;

use App\Repository\ApcComptenceRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\DepartementRepository;
use App\Utils\Files;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetSaesSpecialite extends AbstractController
{
    private ApcSaeRepository $apcSaeRepository;

    public function __construct(ApcSaeRepository $apcSaeRepository)
    {
        $this->apcSaeRepository = $apcSaeRepository;
    }

    public function __invoke(
        DepartementRepository $departementRepository,
        Files                 $files,
        Request $request, $data)
    {
        $annee = (int)$request->query->get('annee', 2022);
        $idDepartement = $request->get('specialite');
        $departement = $departementRepository->findOneBy(['sigle' => $idDepartement]);

        if ($annee !== 2022) {
            throw new \Exception('Année inconnue');
        }

        if ($annee === 2022 && $departement !== null) {
            $fichier = $files->getLastVersionReferentielFile($departement);

            $file = json_decode(file_get_contents($fichier), true);

            return $file;
        }

        return $this->apcSaeRepository->findBySigleDepartement($departement->getSigle());
    }
}
