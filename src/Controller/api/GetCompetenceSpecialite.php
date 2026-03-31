<?php

namespace App\Controller\api;

use App\Repository\ApcComptenceRepository;
use App\Repository\DepartementRepository;
use App\Utils\Files;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetCompetenceSpecialite extends AbstractController
{
    private ApcComptenceRepository $apcComptenceRepository;

    public function __construct(ApcComptenceRepository $apcComptenceRepository)
    {
        $this->apcComptenceRepository = $apcComptenceRepository;
    }

    public function __invoke(
        DepartementRepository $departementRepository,
        Files                 $files, Request $request, $data): array
    {
        $annee = (int)$request->query->get('annee', 2022);
        $idDepartement = $request->get('specialite');
        $departement = $departementRepository->findOneBy(['sigle' => $idDepartement]);

        if ($annee !== 2022) {
            throw new Exception('Année inconnue');
        }

        if ($annee === 2022 && $departement !== null) {

            // chargement du référentiel de compétences depuis le fichier JSON
            $fichier = $files->getVersion2022($departement); //todo: il faudrait gérer une année
            $ancien = json_decode(file_get_contents($fichier), true);

            $tCompetences = [];
            foreach ($ancien['competences'] as $competence) {
                $tCompetences[$competence['id']] = $competence;
            }

            return $tCompetences;
        }

        //todo: gérer la version
        return $this->apcComptenceRepository->findBySigleVersion($departement->getSigle());
    }
}
