<?php

namespace App\Form;

use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Entity\Iut;
use App\Entity\IutSite;
use App\Entity\QapesSae;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QapesSaePart1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('redacteur', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
                'choice_label' => 'display',
                'label' => 'Rédacteur(s)',
                'help' => 'En complément de l\'auteur initial',
            ])
            ->add('iut', EntityType::class, [
                'class' => Iut::class,
                'choice_label' => 'libelle',
                'label' => 'IUT',
                'mapped' => false,
                'attr' => ['data-action' => 'change->qapes#changeIut']
            ])
            ->add('iutSite', EntityType::class, [
                'class' => IutSite::class,
                'choice_label' => 'libelle',
                'label' => 'Site de l\'IUT',
                'required' => false,
                'attr' => ['data-action' => 'change->qapes#changeSiteIut'],
                'disabled' => true,
            ])
            ->add('specialite', EntityType::class, [
                'class' => Departement::class,
                'choice_label' => 'libelle',
                'label' => 'Spécialité',
                'required' => false,
                'disabled' => true,
                'attr' => ['data-action' => 'change->qapes#changeSpecialite'],
            ])
            ->add('parcours', EntityType::class, [
                'class' => ApcParcours::class,
                'choice_label' => 'libelle',
                'label' => 'Parcours',
                'required' => false,
                'attr' => ['data-action' => 'change->qapes#changeParcours'],
                'disabled' => true,
            ])
            ->add('sae', ChoiceType::class, [
                'choices' => [],
                'label' => 'SAE',
                'required' => false,
                'disabled' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QapesSae::class,
        ]);
    }
}
