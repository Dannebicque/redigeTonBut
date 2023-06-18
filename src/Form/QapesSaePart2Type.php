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
use PhpOffice\PhpWord\Shared\Text;
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
                'help' => 'Nom de la SAÉ qui permet de l’identifier et d’en donner une idée de contenu. Ce nom doit être différent du code et nom « officiel », de façon à le rendre spécifique à votre parcours IUT (par exemple : Rénovation des installations thermiques d’un gymnase de Lyon)'
            ])
            ->add('lien', UrlType::class, [
              'label' => 'Lien vers une présentation de la SAÉ',
                'required' => false,
                'help'=> 'Cette présentation permet à l’étudiant de comprendre la nature, les objectifs, le déroulement, les livrables attendus, etc. de la SAÉ et peut prendre la forme d’une fiche de description, d’un PPT (sonorisé ou pas), d’une présentation vidéo, d’un mini-site, des documents distribués aux étudiants, etc.'
            ])
            ->add('aEpingler', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 10,
                ],
                'label' => 'A épingler',
                'help' => 'L’intention ici est de mettre en évidence ce qu’il y a de particulier et d’intéressant dans la SAÉ (particularités en lien avec le public : SAÉ verticale (collaboration étudiants B1 et B3) ou SAÉ qui regroupe des étudiants d’écoles différentes ; particularités en lien avec le résultat : SAÉ qui ouvre sur une production en réponse à une vraie commande, SAÉ valorisée dans une communication à un congrès ; particularité en lien avec les méthodes, etc. ). Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                'help_html' => true,
            ])
            ->add('anneeCreation', null, [
                'label' => 'Année de création de la SAÉ'
            ])
            ->add('version', TextType::class, [
                'label' => 'Version de la SAÉ',
                'required' => false,
                'help' => 'Cette information permet « a posteriori » de retracer l’évolution de la SAE, d’identifier les changements qui y ont été apportés d’une version à l’autre.'
            ])
            ->add('dateVersion', null, [
                'label' => 'Date de la version'
            ])
            ->add('modeDispense', ChoiceType::class, [
                'choices' => [
                    'En présentiel' => 'presentiel',
                    'En distanciel' => 'distanciel',
                    'Hybride' => 'hybride'
                ],
                'label' => 'Organisation de la SAE',
                'help' => 'Précisez ici si la SAÉ est organisée exclusivement en ligne ou exclusivement en présentiel ou encore si elle concilie ces deux modalités. '
            ])
            ->add('nbEcts', NumberType::class, [
                'label' => 'Crédits ECTS',
                'help' => 'Le nombre de crédits informe non seulement sur la charge de travail, mais aussi sur le poids (le crédit) accordé à cette SAÉ.'
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
                'label' => 'Composition de la SAE',
                'help' => 'Précisez ici s’il s’agit d’une activité individuelle ou de groupe'
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
                'help' => 'Les groupes sont-ils hétérogènes (alternants + étudiants) ou homogènes (groupe d\'alternants et groupe d\'étudiants) ? Quels sont les principes qui ont présidé à la constitution des groupes ?. Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                'help_html' => true,
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
                    'rows' => 10,
                ],
                'help' => 'Il s’agit ici de décrire concrètement la mission confiée aux étudiants. Ne soyez pas avares de détails. Il importe que ces quelques lignes donnent envie d’entrer en action et surtout qu’elles permettent d’apprécier la complexité de la tâche. Il est possible d\'utiliser <a href="#" data-bs-toggle="modal" data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                'help_html' => true,
            ])
            ->add('deroulementSae', TextareaType::class, [
                'label' => 'Déroulement de la SAE',
                'required' => false,
                'attr' => [
                    'rows' => 10,
                ],
                'help' => 'Dans cette rubrique, vous êtes invités à décrire les étapes qui permettront aux étudiants de mener à bien leur mission. Il importe d’être concret et de veiller à expliciter les choix auxquels sera confronté l’étudiant. Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                'help_html' => true,
            ])
            ->add('lienLigneDuTemps', UrlType::class, [
              'label' => 'Lien vers la ligne du temps de la SAÉ',
                'required' => false,
                'help' => 'Ligne du temps sur laquelle s\'affichent les actions des étudiants et les actions des enseignants.'
            ])
            ->add('evaluations', TextareaType::class, [
                'label' => 'Modalités d\'Évaluations',
                'required' => false,
                'attr' => [
                    'rows' => 10,
                ],
                'help' => 'L’objectif ici est d’identifier les éléments pris en considération pour statuer sur le développement effectif de la compétence. Précisez sur base de quoi vous allez apprécier la qualité des démarches, la qualité des résultats, la justification, la critique et l’adaptation. Précisez ce qui relève de l’évaluation de groupe et ce qui relève de l’évaluation individuelle. Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                'help_html' => true,
            ])
            ->add('auteur', EntityType::class, [
                'class' => User::class,
                'query_builder' => function(UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC')
                        ->addOrderBy('u.prenom', 'ASC');
                },
                'help' => 'Nom de la (des) personne(s) qui a (ont) conçu la SAÉ. Maintenir ctrl ou cmd pour ajouter plusieurs auteurs',
                'multiple' => true,
                'choice_label' => 'display',
                'label' => 'Rédacteur(s) de la SAE',
            ])
            ->add('auteursAutres', TextareaType::class, [
                'label' => 'Ajouter des rédacteurs absents de la liste',
                'required' => false,
                'mapped' => false,
                'help' => 'Saisir nom; prénom et email, séparés par un ";" (point virgule) des auteurs, un par ligne.'
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
