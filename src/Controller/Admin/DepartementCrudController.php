<?php

namespace App\Controller\Admin;

use App\Entity\Departement;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;

class DepartementCrudController extends BaseCrudController
{
    public function __construct(private Security $security)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Departement::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnIndex()->hideOnForm(),
            NumberField::new('numeroAnnexe')->setLabel('Numéro Annexe')->setDisabled(true),
            TextField::new('sigle')->setLabel('Sigle'),
            TextField::new('typeDepartement')->setDisabled(true)->setLabel('Type de département')->hideOnIndex(),
            TextField::new('typeStructure')->setLabel('Type de structure')->setPermission('ROLE_CPN'),
            TextField::new('libelle')->setLabel('Libellé')->hideOnIndex(),
            TextField::new('pacd')
                ->setLabel('PACD')
                ->formatValue(function ($value, $entity) {
                    if ($entity->getPacd()) {
                        return method_exists($entity->getPacd(), 'display')
                            ? $entity->getPacd()->display()
                            : (string) $entity->getPacd();
                    }

                    return 'Aucun';
                })->hideOnForm(),
            TextareaField::new('textePresentation')->setLabel('Description')->hideOnIndex()->setPermission('ROLE_GT'),
            BooleanField::new('verouilleStructure')->setLabel('Verrouillé Structure')->setPermission('ROLE_GT'),
            BooleanField::new('verouilleCompetences')->setLabel('Verrouillé Compétences')->setPermission('ROLE_GT'),
            BooleanField::new('verouilleCroise')->setLabel('Verrouillé Croisé')->setPermission('ROLE_GT'),
            BooleanField::new('pn_bloque')->setLabel('PN Bloqué')->setPermission('ROLE_GT'),
            AssociationField::new('cpns')
                ->setLabel('CPN')
                ->setFormTypeOptions([
                    'by_reference' => false, // important pour ManyToMany
                    'multiple' => true,
                ])
                ->autocomplete()
            ->setPermission('ROLE_ADMIN'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Département')
            ->setEntityLabelInPlural('Départements')
            ->setDefaultSort(['numeroAnnexe' => 'ASC'])
            ->setSearchFields(['sigle', 'libelle', 'pacd']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        return $actions
            // ...
            ->disable(Action::BATCH_DELETE); // Désactive l'action batch de suppression
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('sigle')
            ->add('libelle')
            ->add('verouilleStructure')
            ->add('verouilleCompetences')
            ->add('verouilleCroise')
            ->add('pn_bloque');
    }

    public function createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters): QueryBuilder
    {
        $user = $this->security->getUser();
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isGranted('ROLE_ADMIN')) {
            //todo: ajouter les PACD ?
            $qb->andWhere(':user MEMBER OF entity.cpns')
                ->setParameter('user', $user);
        }

        return $qb;
    }
}
