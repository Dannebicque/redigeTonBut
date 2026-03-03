<?php

namespace App\Controller\api;

use App\Repository\ApcComptenceRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\DepartementRepository;
use App\Utils\Files;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetRessourcesSpecialite extends AbstractController
{
    private ApcRessourceRepository $apcRessourceRepository;

    public function __construct(ApcRessourceRepository $apcRessourceRepository)
    {
        $this->apcRessourceRepository = $apcRessourceRepository;
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

        //todo: gérer la version
        return $this->apcRessourceRepository->findBySigleVersion($departement->getSigle());
    }
}
