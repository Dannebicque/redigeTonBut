<?php

namespace App\Controller\Admin;

use App\Admin\Filter\DepartementFilter;
use App\Entity\ApcRessource;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Doctrine\ORM\QueryBuilder;

class ApcRessourceCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return ApcRessource::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ressource')
            ->setEntityLabelInPlural('Ressources')
            ->setDefaultSort(['semestre.ordreLmd' => 'ASC', 'ordre' => 'ASC', 'libelle' => 'ASC'])
            ->setSearchFields(['codeMatiere', 'libelle', 'libelleCourt']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('codeMatiere', 'Code'),
            TextField::new('libelle', 'Libellé'),
            AssociationField::new('semestre', 'Semestre'),
            TextField::new('semestre.annee.version.departement.sigle', 'Département')
                ->onlyOnIndex(),
            IntegerField::new('semestre.annee.version.annee', 'Version')
                ->onlyOnIndex(),
            BooleanField::new('isRessourceIA', 'IA'),
            BooleanField::new('isRessourceTEDS', 'TEDS'),
            BooleanField::new('isRessourcePpp', 'PPP'),
            BooleanField::new('isRessourceExpression', 'Expression'),
            BooleanField::new('isRessourceLve', 'LVE'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(DepartementFilter::new())
            ->add(
                ChoiceFilter::new('versionAnnee', 'Version')
                    ->setChoices([
                        '2021' => 2021,
                        '2027' => 2027,
                    ])
                    ->canSelectMultiple(false)
                    ->setFormTypeOption('mapped', false)
            )
            ->add('isRessourceIA')
            ->add('isRessourceTEDS');
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $appliedFilters = $searchDto->getAppliedFilters();
        $version = $appliedFilters['versionAnnee']['value'] ?? null;

        if (isset($appliedFilters['versionAnnee'])) {
            unset($appliedFilters['versionAnnee']);

            $searchDto = new SearchDto(
                $searchDto->getRequest(),
                $searchDto->getSearchableProperties(),
                $searchDto->getQuery(),
                [],
                $searchDto->getSort(),
                $appliedFilters
            );
        }

        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if ($version !== null && $version !== '') {
            $qb
                ->leftJoin('entity.semestre', 's')
                ->leftJoin('s.annee', 'a')
                ->leftJoin('a.version', 'v')
                ->andWhere('v.annee = :version')
                ->setParameter('version', $version);
        }

        return $qb;
    }

}
