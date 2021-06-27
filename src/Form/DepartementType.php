<?php

namespace App\Form;

use App\Entity\Departement;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sigle')
            ->add('libelle')
            ->add('numeroAnnexe', IntegerType::class)
            ->add('typeDepartement', ChoiceType::class,
                ['choices' => ['secondaire' => 'secondaire', 'tertiaire' => 'tertiaire']])
            ->add('pacd', EntityType::class, ['class' => User::class, 'choice_label' => 'display'])
            ->add('cpn', EntityType::class, ['class' => User::class, 'choice_label' => 'display']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Departement::class,
        ]);
    }
}
