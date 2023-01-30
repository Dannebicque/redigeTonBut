<?php

namespace App\Form;

use App\Entity\QapesCriteresEvaluation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QapesCriteresEvaluation1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('valeurs', null, [
                'label' => 'Valeurs',
                'help' => 'Séparer les valeurs par une virgule',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QapesCriteresEvaluation::class,
        ]);
    }
}
