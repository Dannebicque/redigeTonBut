<?php


// src/Admin/Filter/DepartementFilter.php

namespace App\Admin\Filter;

use App\Entity\Departement;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DepartementFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName = 'departement', ?string $label = 'Département'): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(EntityType::class)
            ->setFormTypeOption('class', Departement::class)
            ->setFormTypeOption('choice_label', 'libelle')
            ->setFormTypeOption('placeholder', 'Tous')
            ->setFormTypeOption('required', false)
            ->setFormTypeOption('mapped', false);
    }

    public function apply(
        QueryBuilder  $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto     $fieldDto,
        EntityDto     $entityDto
    ): void
    {
        $value = $filterDataDto->getValue();

        if (null === $value || '' === $value) {
            return;
        }

        $rootAlias = $filterDataDto->getEntityAlias();

        // Adapte les noms de relations à ton modèle
        $queryBuilder
            ->leftJoin($rootAlias . '.semestre', 'semestre_filter')
            ->leftJoin('semestre_filter.annee', 'annee_filter')
            ->leftJoin('annee_filter.departement', 'departement_filter')
            ->andWhere('departement_filter = :departement')
            ->setParameter('departement', $value);
    }
}
