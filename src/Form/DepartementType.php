<?php

namespace App\Form;

use App\Entity\Departement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartementType extends AbstractType
{
    private bool $droit;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->droit =  $options['droit'];
        $builder
            ->add('sigle')
            ->add('libelle')
            ->add('numeroAnnexe', IntegerType::class, ['disabled' => $this->droit])
            ->add('altBut1', TextType::class, ['label' => 'Pourcentage de réduction pour l\'alternance en BUT1'])
            ->add('altBut2', TextType::class, ['label' => 'Pourcentage de réduction pour l\'alternance en BUT2'])
            ->add('altBut3', TextType::class, ['label' => 'Pourcentage de réduction pour l\'alternance en BUT3'])
            ->add('numeroAnnexe', IntegerType::class, ['disabled' => $this->droit])
            ->add('numeroAnnexe', IntegerType::class, ['disabled' => $this->droit])
            ->add('typeDepartement', ChoiceType::class,
                ['choices' => ['Secondaire' => 'secondaire', 'Tertiaire' => 'tertiaire'], 'disabled' => $this->droit])
            ->add('typeStructure', ChoiceType::class,
                ['choices' => ['Type 1' => Departement::TYPE1, 'Type 2' => Departement::TYPE2, 'Type 3' => Departement::TYPE3], 'disabled' => $this->droit])
            ->add('textePresentation', TextareaType::class,
                ['label' => 'Texte descriptif figurant dans l\'annexe',
                    'help' => 'Objectifs du diplôme, intitulé des parcours, métiers et secteurs d’activités visés, compétences visées.',
                    'attr' => ['rows' => 50]])
           ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Departement::class,
            'droit' => null
        ]);
    }
}
