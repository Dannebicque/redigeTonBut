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

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choix = [
            'Editeur' => 'ROLE_EDIT',
            'Lecteur' => 'ROLE_LECTEUR',
            'Secrétaire de CPN' => 'ROLE_CPN',
            'PACD' => 'ROLE_PACD',
        ];
        if ($options['droit_gt'] === true) {
            $choix['Membre du GT'] = 'ROLE_GT';
        }

        ksort($choix);


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
                'choices' => $choix
            ])
            ->add('departement', EntityType::class,
                ['label' => 'Spécialité',
                    'required' => false,
                    'class' => Departement::class,
                    'choice_label' => 'sigle'])
            ->add('actif', ChoiceType::class, [
                'choices' => ['Activé' => true, 'Désactivé'=>false],
                'expanded' => true,
                'label' => 'Etat du compte'
            ])
        ;

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
            'droit_gt' => null,
        ]);
    }
}
