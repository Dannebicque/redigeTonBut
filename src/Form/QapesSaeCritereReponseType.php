<?php

namespace App\Form;

use App\Entity\QapesCritere;
use App\Entity\QapesSaeCritereReponse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QapesSaeCritereReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('critere', EntityType::class, [
                'class' => QapesCritere::class,
                'choice_label' => 'libelle',
                'disabled' => true,
            ])
            ->add('commentaire')

//            ->add('sae')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QapesSaeCritereReponse::class,
        ]);
    }
}
