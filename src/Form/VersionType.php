<?php

namespace App\Form;

use App\Entity\Departement;
use App\Entity\Version;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionType extends AbstractType
{
    private bool $droit;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->droit =  $options['droit'];
        $builder
            ->add('altBut1', TextType::class, ['label' => 'Pourcentage de réduction pour l\'alternance en BUT1'])
            ->add('altBut2', TextType::class, ['label' => 'Pourcentage de réduction pour l\'alternance en BUT2'])
            ->add('altBut3', TextType::class, ['label' => 'Pourcentage de réduction pour l\'alternance en BUT3'])
            ->add('textePresentation', TextareaType::class,
                ['label' => "Texte descriptif figurant dans l'annexe",
                    'help' => 'Objectifs du diplôme, intitulé des parcours, métiers et secteurs d’activités visés, compétences visées.',
                    'attr' => ['rows' => 50]])
           ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Version::class,
            'droit' => true
        ]);
    }
}
