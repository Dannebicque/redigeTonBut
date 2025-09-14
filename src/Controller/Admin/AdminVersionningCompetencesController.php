<?php

namespace App\Controller\Admin;

use App\Classes\CompareJson;
use App\Classes\Export\DepartementExport;
use App\Repository\DepartementRepository;
use App\Utils\Files;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminVersionningCompetencesController extends AbstractController
{
    #[Route('/admin/versionning/competences', name: 'administration_versionning_competences')]
    public function index(
        DepartementRepository $departementRepository,
    ): Response
    {
        // filtrer les départements pour n'afficher que ceux auxquels l'utilisateur a accès
        $user = $this->getUser();
        if ($this->isGranted('ROLE_GT')) {
            // Si l'utilisateur est GT, on affiche tous les départements
            $departements = $departementRepository->findAll();
        } else {
            // Sinon, on filtre les départements en fonction des droits de l'utilisateur
            $departements = $departementRepository->findByUser($user);
        }


        return $this->render('admin_versionning_competences/index.html.twig', [
            'departements' => $departements,
        ]);
    }

    #[Route('/admin/versionning/competences/update', name: 'administration_versionning_competences_update')]
    public function update(
        Files $files,
        CompareJson $compareJson,
        DepartementExport $departementExport,
        Request $request,
        DepartementRepository $departementRepository,
    ): Response
    {
        $departement = $departementRepository->find($request->request->get('departement'));

        if (!$departement) {
            throw $this->createNotFoundException('Département non trouvé.');
        }
        $filePath = $files->getLastVersionFile($departement);
        $tabAncien = json_decode(file_get_contents($filePath), true);

        // encoder le référentiel de compétences en base de données en JSON
        $tabActuel = $departementExport->genereJson($departement);
        // comparer les deux fichiers
        $compareJson->setTabAncien($tabAncien);
        $compareJson->setTabActuel($tabActuel);
        $compareJson->compare();

        // récupérer les différences
        $diff = $compareJson->getDiff();

        return $this->render('admin_versionning_competences/_compare.html.twig', [
            'departement' => $departement,
            'diff' => $diff,
        ]);
    }
}
