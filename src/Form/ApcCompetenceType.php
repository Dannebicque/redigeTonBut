<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Form/ApcCompetenceType.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 11/03/2021 17:26
 */

namespace App\Form;

use App\Entity\ApcCompetence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcCompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom_court', TextType::class, ['help' => 'Mot désignant la compétence. 50 caractères maximum.', 'attr' => ['maxlength' => 50]])
            ->add('libelle', TextType::class, ['help' => 'Libellé long de la compétence', 'disabled' => true])
            ->add('couleur', ChoiceType::class, [
                'choices' => [
                    '1' => 'c1',
                    '2' => 'c2',
                    '3' => 'c3',
                    '4' => 'c4',
                    '5' => 'c5',
                    '6' => 'c6',
                ],
                'expanded' => true,
                'label' => 'Ordre de la compétence',
                'help'  => 'Si une compétence occupe déjà la place elles seront inversées'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApcCompetence::class,
        ]);
    }
}
