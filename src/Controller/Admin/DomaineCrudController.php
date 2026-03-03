<?php

namespace App\Controller\Admin;

use App\Entity\Domaine;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DomaineCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Domaine::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('url', 'URL du domaine'),
        ];
    }
}
