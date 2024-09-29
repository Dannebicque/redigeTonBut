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
            'IUT' => 'ROLE_IUT',
            'Labset' => 'ROLE_LABSET',
            'Editeur' => 'ROLE_EDITEUR',
            'Lecteur' => 'ROLE_LECTEUR',
            'Secrétaire de CPN (lecture/écriture)' => 'ROLE_CPN',
            'Membre CPN (lecture)' => 'ROLE_CPN_LECTEUR',
            'PACD' => 'ROLE_PACD',
        ];

        if ($options['droit_gt'] === true) {
            $choix['Membre du GT'] = 'ROLE_GT';
            $choix['Membre DGESIP'] = 'ROLE_DGESIP';
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
                'choices' => $choix,
                'disabled' => !$options['droit_gt'],
            ])
            ->add('departement', EntityType::class,
                ['label' => 'Spécialité',
                    'required' => false,
                    'class' => Departement::class,
                    'disabled' => !$options['droit_gt'],
                    'choice_label' => 'sigle'])
            ->add('actif', ChoiceType::class, [
                'choices' => ['Activé' => true, 'Désactivé'=>false],
                'expanded' => true,
                'label' => 'Etat du compte'
            ])
            ->add('CpnDepartements', EntityType::class,
                ['label' => 'Départements CPN',
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true,
                    'help' => 'Uniquement pour les membres de la CPN (qui ne sont pas dans le GT)',
                    'class' => Departement::class,
                    'disabled' => !$options['droit_gt'],
                    'choice_label' => 'sigle']);

        if ($options['droit_gt'] === true) {
            $builder->add('isVerified', ChoiceType::class,
                [
                    'choices' => ['Vérifié' => true, 'Non vérifié' => false],
                    'expanded' => true,
                    'label' => 'Vérification de l\'email'
                ]);
        }


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
