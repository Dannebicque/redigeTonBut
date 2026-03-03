<?php

namespace App\Controller\Admin;

use App\Entity\ApcCompetence;
use App\Entity\Departement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ApcCompetenceCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return ApcCompetence::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('departement')
                ->setLabel('Département')
                ->setCrudController(Departement::class)
                ->hideOnForm()
                ->setRequired(true),
            TextField::new('nom_court', 'Nom court'),
            TextField::new('libelle', 'Libellé'),
            TextField::new('couleur', 'Couleur'),
            IntegerField::new('numero', 'Numéro'),
            IntegerField::new('numero_identifiant', 'Numéro Unique'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('departement'));
    }
}
