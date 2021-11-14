<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Form/ApcSaeType.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 31/05/2021 20:35
 */

namespace App\Form;

use App\Entity\ApcCompetence;
use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Repository\ApcComptenceRepository;
use App\Repository\SemestreRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcSaeType extends AbstractType
{
    protected ?Departement $departement;
    protected ?ApcParcours $parcours;
    private bool $editable;
    private bool $verouille_croise;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->departement = $options['departement'];
        $this->editable = !$options['editable'];
        $this->verouille_croise = $options['verouille_croise'];
        $this->parcours = $options['parcours'];

        $builder
            ->add('codeMatiere', TextType::class, ['label' => 'Code SAÉ',  'disabled' => $this->editable, 'help' => 'Code généré automatiquement'])
            ->add('ficheAdaptationLocale', ChoiceType::class, ['label' => 'Fiche d\'adaptation locale ?', 'expanded' => true, 'choices' => ['Oui' => true, 'Non' => false,], 'attr' => ['class' => 'text-white'], 'label_attr' => ['class' => 'text-white'],'help' => 'Si la fiche est de l\'adaptation locale, elle ne sera pas prise en compte dans les tableaux et simplement affichées aux collègues', 'help_attr' => ['class' => 'text-white']])
            ->add('libelle', TextType::class, ['label' => 'Nom de la SAÉ', 'disabled' => $this->verouille_croise])
            ->add('portfolio', ChoiceType::class, ['label' => 'SAÉ du portfolio', 'expanded' => true, 'choices' => ['Oui' => true, 'Non' => false], 'disabled' => $this->verouille_croise])
            ->add('stage', ChoiceType::class, ['label' => 'SAÉ du stage', 'expanded' => true, 'choices' => ['Oui' => true, 'Non' => false], 'disabled' => $this->verouille_croise])
            ->add('ordre', NumberType::class, ['label' => 'Ordre dans le semestre', 'disabled' => $this->verouille_croise])
            ->add('libelleCourt', TextType::class,
                ['label' => 'Libellé court', 'required' => false, 'attr' => ['maxlength' => 25], 'help' => '25 caractères maximum, peut être utile pour Apogée', 'disabled' => $this->verouille_croise])
            ->add('description', TextareaType::class,
                [
                    'attr' => ['rows' => 20],
                    'label' => 'Descriptif générique',
                    'required' => false,
                    'help' => 'Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                    'help_html' => true,
                ])
            ->add('objectifs', TextareaType::class,
                [
                    'attr' => ['rows' => 20],
                    'label' => 'Objectifs et problématique professionnelle',
                    'required' => false,
                    'help' => 'Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                    'help_html' => true,
                ])
            ->add('exemples', TextareaType::class,
                [
                    'attr' => ['rows' => 20],
                    'label_attr' => ['class' => 'text-white'],
                    'help_attr' => ['class' => 'text-white'],
                    'label' => 'Exemple(s) de mise en pratique',
                    'required' => false,
                    'help' => 'Cette zone est indicative et ne sera pas publiée dans le référentiel de formation. Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                    'help_html' => true,
                ])
            ->add('heuresTotales', TextType::class, ['label' => 'Préconisation Heures totales',
                'help' => 'A titre indicatif pour les départements.',
                'required' => false,
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])
            ->add('tpPpn', TextType::class, ['label' => 'Préconisation Dont TP',
                'required' => false,
                'help' => 'A titre indicatif pour les départements.',
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])
            ->add('projetPpn', TextType::class, ['label' => 'Préconisation "projet tutoré"',
                'required' => false,
                'help' => 'A titre indicatif pour les départements.',
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])

            ->add('semestre', EntityType::class, [
                'class' => Semestre::class,
                'required' => true,
                'choice_label' => 'display',
                'attr' => ['x-model'=> 'semestre', '@change' => 'changeSemestre'],
                'query_builder' => function(SemestreRepository $semestreRepository) {
                    return $semestreRepository->findByDepartementParcoursBuilder($this->departement, $this->parcours);
                },
                'label' => 'Semestre',
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApcSae::class,
            'departement' => null,
            'editable' => null,
            'verouille_croise' => null,
            'parcours' => null,
        ]);
    }
}
