<?php

namespace App\Form;

use App\Entity\QapesCritere;
use App\Form\Type\CollectionStimulusType;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QapesCritereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé du critère',
            ])
            ->add('libelleAffichage', TextType::class, [
                'label' => 'Libellé d\'affichage',
                'help' => 'Libellé affiché dans les graphiques et le détail de la fiche SAE',
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'help' => 'Description du critère affiché dans le formulaire de collecte',
                'required' => false,
            ])
            ->add('qapesCritereReponses', CollectionStimulusType::class, [
                'entry_type' => QapesCritereReponseType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QapesCritere::class,
        ]);
    }
}
