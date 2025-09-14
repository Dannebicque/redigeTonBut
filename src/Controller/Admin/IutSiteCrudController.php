<?php

namespace App\Controller\Admin;

use App\Entity\IutSite;
use App\Entity\IutVille;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class IutSiteCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return IutSite::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('libelle', "Nom de l'IUT"),
            AssociationField::new('ville', 'Ville')->setCrudController(
                IutVille::class
            ),
        ];
    }
}
