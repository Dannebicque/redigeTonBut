<?php

namespace App\Controller;

use App\Classes\PN\GenerePdfTableaux;
use App\Classes\Tableau\Structure;
use App\Entity\Departement;
use App\Repository\DepartementRepository;
use App\Repository\SemestreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LaurentController extends AbstractController
{
    #[Route('/laurent/structure/{specialite}', name: 'laurent_structure')]
    public function index(
        DepartementRepository $departementRepository,
        SemestreRepository $semestreRepository,
        Structure $structure,
        $specialite): Response
    {

        $departement = $departementRepository->findOneBy(['sigle' => $specialite]);
        if ($departement->getTypeStructure() === Departement::TYPE3) {
            $parcours = $departement->getApcParcours();
            foreach ($parcours as $parcour) {
                $semestres = $semestreRepository->findByParcours($parcour);
            }
        } else {
            $parcours = null;
            $semestres = $departement->getSemestres();
        }

        $json = $structure->setSemestres($semestres)->setDepartement($departement)->getDataJson();

       return $this->render('pdf/tableau-structure.html.twig', [
            'departement' => $departement,
            'donnees' => $json,
            'parcours' => $parcours !== null ? $parcours : null,
        ]);
    }
}
