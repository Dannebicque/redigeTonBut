<?php

namespace App\Form;

use App\Entity\Departement;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choix = [
            'Editeur' => 'ROLE_EDIT',
            'Lecteur' => 'ROLE_LECTEUR',
            'Membre du GT' => 'ROLE_GT',
            'Secrétaire de CPN' => 'ROLE_CPN',
            'PACD' => 'ROLE_PACD',
        ];

        $builder
            ->add('civilite', ChoiceType::class, [
                'choices' => ['M.' => 'M.', 'Mme' => 'Mme'],
                'expanded' => true,
                'label' => 'Civilité'
            ])
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('email', EmailType::class, ['label' => 'email'])
            ->add('roles', ChoiceType::class, [
                'label' => 'Autorisations',
                'choices' => $choix,
                'disabled' => true,
                'help' => 'Si vous constatez une erreur, contactez le PACD ou le secrétaire de CPN'
            ])
            ->add('departement', EntityType::class,
                [
                    'label' => 'Spécialité',
                    'required' => false,
                    'class' => Departement::class,
                    'choice_label' => 'sigle',
                    'disabled' => true,
                    'help' => 'Si vous constatez une erreur, contactez le PACD ou le secrétaire de CPN'
                ]);

        // Data transformer
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray) ? $rolesArray[0] : null;
                },
                function($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
