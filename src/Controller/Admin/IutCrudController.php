<?php

namespace App\Controller\Admin;

use App\Entity\Iut;
use App\Entity\IutUniversite;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class IutCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Iut::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('libelle', "Nom de l'IUT"),
            AssociationField::new('universite', 'Université')->setCrudController(
                IutUniversite::class
            ),
        ];
    }
}
