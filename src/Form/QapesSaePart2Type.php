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

class QapesSaePart2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intituleSae')
            ->add('lien')
            ->add('aEpingler', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
                'label' => 'A épingler',
            ])
            ->add('anneeCreation')
            ->add('version')
            ->add('dateVersion')
            ->add('modeDispense',ChoiceType::class, [
                'choices' => [
                    'En présentiel' => 'présentiel',
                    'En distanciel' => 'distanciel',
                    'Hybride' => 'hybride'
                ],
                'label' => 'Organisation de la SAE'
            ])
            ->add('nbEcts')
            ->add('typeSae', ChoiceType::class, [
                'choices' => [
                    'Serious game' => 'serious game',
                    'Simulation' => 'simulation',
                    'Cas' => 'cas',
                    'Projet' => 'projet',
                    'Problème' => 'problème',
                    'Pas précisé' => 'pas précisé',
                ],
                'label' => 'Type de SAE',
            ])
            ->add('saeGroupeIndividuelle', ChoiceType::class, [
                'choices' => [
                    'Individuelle' => 'Individuelle',
                    'En Groupe' => 'Groupe',
                ],
                'label' => 'Composition de la SAE'
            ])
            ->add('publicCible', ChoiceType::class, [
                'choices' => [
                    'Etudiants' => 'Etudiants',
                    'Alternants' => 'Alternants',
                    'Mixte' => 'Mixte',
                ],
                'label' => 'Public cible'
            ])
            ->add('publicCibleCommentaire', TextareaType::class, [
                'label' => 'Commentaire sur le public cible',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('nbEtudiants')
            ->add('nbEncadrants')
            ->add('nbHeuresAutonomie')
            ->add('nbHeuresDirigees')
            ->add('objectifsSae', TextareaType::class, [
                'label' => 'Objectifs de la SAE',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('deroulementSae', TextareaType::class, [
                'label' => 'Dérroulement de la SAE',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('lienLigneDuTemps')
            ->add('evaluations', TextareaType::class, [
                'label' => 'Modalités d\'Évaluations',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
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
