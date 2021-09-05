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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcSaeType extends AbstractType
{
    protected ?Departement $departement;
    private bool $editable;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->departement = $options['departement'];
        $this->editable = !$options['editable'];

        $builder
            ->add('codeMatiere', TextType::class, ['label' => 'Code SAÉ',  'disabled' => $this->editable, 'help' => 'Code généré automatiquement'])
            ->add('libelle', TextType::class, ['label' => 'Nom de la SAÉ'])
            ->add('ordre', NumberType::class, ['label' => 'Ordre dans le semestre'])
            ->add('libelleCourt', TextType::class,
                ['label' => 'Libellé court', 'required' => false, 'attr' => ['maxlength' => 25], 'help' => '25 caractères maximum, peut être utile pour Apogée'])
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
            ->add('tdPpn', TextType::class, ['label' => 'Préconisation TD',
                'help' => 'A titre indicatif pour les départements.',
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])
            ->add('cmPpn', TextType::class, ['label' => 'Préconisation CM', 'help' => 'A titre indicatif pour les départements.',
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])
            ->add('tpPpn', TextType::class, ['label' => 'Préconisation  TP','help' => 'A titre indicatif pour les départements.',
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])
            ->add('projetPpn', TextType::class, ['label' => 'Préconisation "projet tutoré"','help' => 'A titre indicatif pour les départements.',
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])

            ->add('semestre', EntityType::class, [
                'class' => Semestre::class,
                'required' => true,
                'choice_label' => 'display',
                'attr' => ['x-model'=> 'semestre', '@change' => 'changeSemestre'],
                'query_builder' => function(SemestreRepository $semestreRepository) {
                    return $semestreRepository->findByDepartementBuilder($this->departement);
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
        ]);
    }
}
