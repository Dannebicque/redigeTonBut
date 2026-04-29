<?php

namespace App\Controller\Admin;

use App\Entity\ApcCompetence;
use App\Entity\Domaine;
use App\Entity\PdfDocument;
use App\Entity\PdfJob;
use App\Entity\Semestre;
use App\Entity\User;
use App\Entity\Version;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/administration', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('Admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('ORéBUT');
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Retour au site', 'fa fa-arrow-left', '/');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Gestion des BUT');
        yield MenuItem::linkTo(DepartementCrudController::class, 'Départements', 'fas fa-list');
      yield MenuItem::linkTo(VersionCrudController::class, 'Version 2021', 'fas fa-list')->setQueryParameter('annee', 2021);
        yield MenuItem::linkTo(VersionCrudController::class, 'Version 2027', 'fas fa-list')->setQueryParameter('annee', 2027);
        yield MenuItem::linkTo(ApcParcoursCrudController::class, 'Parcours', 'fas fa-list');

        if ($this->isGranted('ROLE_GT')) {
            yield MenuItem::section('Gestion des IUT');
            yield MenuItem::linkTo(IutCrudController::class, 'IUT', 'fas fa-list');
            yield MenuItem::linkTo(IutSiteCrudController::class, 'IUT - Site', 'fas fa-list');
            yield MenuItem::linkTo(IutSiteParcoursCrudController::class, 'IUT - Site / Parcours', 'fas fa-list');
        }

        yield MenuItem::section('Voir les modifications');
        yield MenuItem::linkToRoute('Ref de compétences', 'fa fa-not-equal', 'administration_versionning_competences');

        yield MenuItem::section('Exports');
        yield MenuItem::linkToRoute('Choix des exports', 'fa fa-file', 'admin_apc_referentiel_export');

        yield MenuItem::section('Gestion des utilisateurs');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fas fa-list');
        if ($this->isGranted('ROLE_GT')) {
            yield MenuItem::linkTo(DomaineCrudController::class, 'Domaines autorisés', 'fas fa-list');
        }

        if ($this->isGranted('ROLE_ADMIN')) {

            yield MenuItem::section('Administration');
            yield MenuItem::linkTo(ApcCompetenceCrudController::class, 'Compétences', 'fas fa-list');
            yield MenuItem::linkTo(ApcRessourceCrudController::class, 'Ressources', 'fas fa-list');
            yield MenuItem::linkTo(SemestreCrudController::class, 'Semestres', 'fas fa-list');
            yield MenuItem::linkTo(PdfJobCrudController::class, 'PDF Job', 'fas fa-print');
            yield MenuItem::linkTo(PdfDocumentCrudController::class, 'PDF Documents', 'fas fa-download');
        }
    }
}
