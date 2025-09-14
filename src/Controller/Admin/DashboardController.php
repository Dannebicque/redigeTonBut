<?php

namespace App\Controller\Admin;

use App\Entity\ApcCompetence;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Domaine;
use App\Entity\Iut;
use App\Entity\IutSite;
use App\Entity\IutSiteParcours;
use App\Entity\User;
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
        yield MenuItem::linkToCrud('Départements', 'fas fa-list', Departement::class);
        yield MenuItem::linkToCrud('Parcours', 'fas fa-list', ApcParcours::class);

        if ($this->isGranted('ROLE_GT')) {
            yield MenuItem::section('Gestion des IUT');
            yield MenuItem::linkToCrud('IUT', 'fas fa-list', Iut::class);
            yield MenuItem::linkToCrud('IUT - Site', 'fas fa-list', IutSite::class);
            yield MenuItem::linkToCrud('IUT - Site / Parcours', 'fas fa-list', IutSiteParcours::class);
        }

        yield MenuItem::section('Voir les modifications');
        yield MenuItem::linkToRoute('Ref de compétences', 'fa fa-not-equal', 'administration_versionning_competences');

        yield MenuItem::section('Exports');
        yield MenuItem::linkToRoute('Choix des exports', 'fa fa-file', 'administration_apc_referentiel_export');

        yield MenuItem::section('Gestion des utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-list', User::class);
        if ($this->isGranted('ROLE_GT')) {
            yield MenuItem::linkToCrud('Domaines autorisés', 'fas fa-list', Domaine::class);
        }

        if ($this->isGranted('ROLE_ADMIN')) {

            yield MenuItem::section('Administration');
            yield MenuItem::linkToCrud('Compétences', 'fas fa-list', ApcCompetence::class);
        }
    }
}
