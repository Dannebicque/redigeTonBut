<?php

namespace App\Controller\Admin;

use App\Entity\Semestre;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SemestreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Semestre::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            IntegerField::new('ordreLmd', 'Ordre LMD'),
            TextField::new('version', 'Version')
                ->formatValue(function($value, $entity) {
                    return $entity->getVersion()?->getLibelle(); // affiche le libellé de la version
                }),
            TextField::new('departement', 'Département')
                ->formatValue(function($value, $entity) {
                    return $entity->getDepartement()?->getLibelle(); // affiche le libellé du département
                }),
            AssociationField::new('apcParcours', 'Parcours ?')
                ->setCrudController(ApcParcoursCrudController::class),
        ];
    }

}
