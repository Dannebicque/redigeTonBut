<?php

namespace App\Controller\Admin;

use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class VersionCrudController extends AbstractCrudController
{
    private RequestStack $requestStack;

    public function __construct(private readonly Security $security, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    public static function getEntityFqcn(): string
    {
        return Version::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Titre dynamique (edit/show) basé sur __toString()
            ->setPageTitle(Crud::PAGE_EDIT, fn (?Version $version) => $version ? (string) $version : 'Modifier')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (?Version $version) => $version ? (string) $version : 'Détail')
            // "désactive" la pagination en pratique (toutes les lignes sur une page)
            ->setPaginatorPageSize(1000);
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnIndex()->hideOnForm(),
            TextField::new('departement')->setLabel('Sigle du département')
                ->formatValue(function ($value, $entity) {
                    $dept = method_exists($entity, 'getDepartement') ? $entity->getDepartement() : null;
                    if ($dept) {
                        return method_exists($dept, 'getSigle') ? $dept->getSigle() : (string)$dept;
                    }
                    return 'Aucun';
                })->hideOnForm(),
            TextField::new('etatPublication')->setLabel('Etat publication'),
            BooleanField::new('actif')->setLabel('Version active')->setPermission('ROLE_GT'),
            TextareaField::new('textePresentation')->setLabel('Description')->hideOnIndex()->setPermission('ROLE_GT'),
            BooleanField::new('verouilleStructure')->setLabel('Verrouiller Structure')->setPermission('ROLE_GT'),
            BooleanField::new('textesVerouilles')->setLabel('Verrouiller textes')->setPermission('ROLE_GT'),
            BooleanField::new('verouilleCompetences')->setLabel('Verrouiller Compétences')->setPermission('ROLE_GT'),
            BooleanField::new('verouilleCroise')->setLabel('Verrouiller Croisé')->setPermission('ROLE_GT'),
            BooleanField::new('coeffVerouille')->setLabel('Verrouiller Coeff.')->setPermission('ROLE_GT'),
            BooleanField::new('pnVerouille')->setLabel('PN Bloqué')->setPermission('ROLE_GT'),
            AssociationField::new('previousVersion')->setCrudController(Version::class)->setPermission('ROLE_GT'),
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->security->getUser();

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $annee = $request->query->get('annee');
            if ($annee !== null && $annee !== '') {
                $alias = $qb->getRootAliases()[0];
                $qb->andWhere(sprintf('%s.annee = :annee', $alias))
                    ->setParameter('annee', $annee);
            }
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            //todo: ajouter les PACD ?
            $qb->innerJoin('entity.departement', 'd');
            $qb->andWhere(':user MEMBER OF d.cpns')
                ->setParameter('user', $user);
        }

        return $qb;
    }
}
