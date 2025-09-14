<?php

namespace App\Controller\Admin;

use App\Entity\ApcParcours;
use App\Entity\IutSite;
use App\Entity\IutSiteParcours;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class IutSiteParcoursCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return IutSiteParcours::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('site', 'Site IUT')->setCrudController(
                IutSite::class
            ),
            AssociationField::new('parcours', 'Parcours BUT')->setCrudController(
                ApcParcours::class
            ),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['site' => 'ASC'])
            ->setSearchFields(['site.libelle', 'site.ville.libelle', 'parcours.departement.sigle', 'parcours.departement.libelle', 'parcours.libelle', 'parcours.code']); // Recherche sur le nom du site et de la ville

    }
}
