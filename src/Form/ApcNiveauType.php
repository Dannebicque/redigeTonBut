<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Form/ApcNiveauType.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 11/03/2021 17:33
 */

namespace App\Form;

use App\Entity\Annee;
use App\Entity\ApcNiveau;
use App\Entity\Departement;
use App\Repository\AnneeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcNiveauType extends AbstractType
{
    protected ?Departement $departement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->departement = $options['departement'];
        $builder
            ->add('libelle')
            ->add('ordre', ChoiceType::class,
                ['choices' => ['Niveau 1' => 1, 'Niveau 2' => 2, 'Niveau 3' => 3], 'expanded' => true])
            ->add('annee', EntityType::class, [
                'class' => Annee::class,
                'label' => 'Année de BUT',
                'choice_label' => 'libelle',
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'query_builder' => function (AnneeRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.departement = :departement')
                        ->setParameter('departement', $this->departement)
                        ->orderBy('a.libelle', 'ASC');
                }
            ])
            ->add('apcApprentissageCritiques', CollectionType::class, [
                'entry_type' => ApcApprentissageCritiqueType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'prototype' => true,
                'allow_delete' => true,
                'label' => 'Apprentissages critiques du niveau de compétence',
                'by_reference' => false,
                'prototype_name' => '__niveau__',
                'attr' => [
                    'class' => 'selector-apprentissageCritique',
                ],
                'help' => 'Ajoutez les apprentissages critiques du niveau de compétence.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApcNiveau::class,
            'departement' => null
        ]);
    }
}
