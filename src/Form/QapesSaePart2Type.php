<?php

namespace App\Form;

use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Entity\Iut;
use App\Entity\IutSite;
use App\Entity\QapesSae;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QapesSaePart2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intituleSae', TextType::class, [
                'label' => 'Intitulé de la SAÉ',
                'help' => 'Un nom clair, simple, permettant de parler de cette SAÉ entre collègues'
            ])
            ->add('lien', UrlType::class, [
              'label' => 'Lien vers une présentation de la SAÉ'
            ])
            ->add('aEpingler', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
                'label' => 'A épingler',
            ])
            ->add('anneeCreation', null, [
                'label' => 'Année de création de la SAÉ'
            ])
            ->add('version')
            ->add('dateVersion', null, [
                'label' => 'Date de la version'
            ])
            ->add('modeDispense', ChoiceType::class, [
                'choices' => [
                    'En présentiel' => 'presentiel',
                    'En distanciel' => 'distanciel',
                    'Hybride' => 'hybride'
                ],
                'label' => 'Organisation de la SAE'
            ])
            ->add('nbEcts', NumberType::class, [
                'label' => 'Crédits ECTS'
            ])
            ->add('typeSae', ChoiceType::class, [
                'choices' => [
                    'Serious game' => 'serious game',
                    'Simulation' => 'simulation',
                    'Cas' => 'cas',
                    'Projet' => 'projet',
                    'Problème' => 'probleme',
                    'Pas précisé' => 'pas precise',
                ],
                'label' => 'Type de SAE',
            ])
            ->add('saeGroupeIndividuelle', ChoiceType::class, [
                'choices' => [
                    'Individuelle' => 'individuelle',
                    'En Groupe' => 'groupe',
                ],
                'label' => 'Composition de la SAE'
            ])
            ->add('publicCible', ChoiceType::class, [
                'choices' => [
                    'Etudiants' => 'etudiants',
                    'Alternants' => 'alternants',
                    'Mixte' => 'mixte',
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
            ->add('nbEtudiants', null, [
                'label' => 'Nombre d’étudiants'
            ])
            ->add('nbEncadrants', null, [
              'label' => 'Nombre d’encadrants'
            ])
            ->add('nbHeuresAutonomie', null, [
              'label' => 'Nombre d’heures en autonomie'
            ])
            ->add('nbHeuresDirigees', null, [
              'label' => 'Nombre d’heures dirigées'
            ])
            ->add('objectifsSae', TextareaType::class, [
                'label' => 'Objectifs de la SAE',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('deroulementSae', TextareaType::class, [
                'label' => 'Déroulement de la SAE',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('lienLigneDuTemps', UrlType::class, [
              'label' => 'Lien vers la ligne du temps de la SAÉ'
            ])
            ->add('evaluations', TextareaType::class, [
                'label' => 'Modalités d\'Évaluations',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('redacteur', EntityType::class, [
                'class' => User::class,
                'query_builder' => function(UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC')
                        ->addOrderBy('u.prenom', 'ASC');
                },
                'multiple' => true,
                'choice_label' => 'display',
                'label' => 'Rédacteur(s) de la SAE',
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
