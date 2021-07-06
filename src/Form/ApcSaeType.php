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
        $this->editable = $options['editable'];

        $builder
            ->add('codeMatiere', TextType::class, ['label' => 'Code SAÉ',  'disabled' => $this->editable, 'help' => 'Code généré automatiquement'])
            ->add('libelle', TextType::class, ['label' => 'Libellé'])
            ->add('ordre', NumberType::class, ['label' => 'Ordre dans le semestre'])
            ->add('libelleCourt', TextType::class,
                ['label' => 'Libellé court', 'required' => false, 'attr' => ['maxlength' => 25], 'help' => '25 caractères maximum'])
            ->add('description', TextareaType::class,
                [
                    'attr' => ['rows' => 20],
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Il est possible d\'utiliser la syntaxe Markdown dans ce bloc de texte',
                ])
            ->add('tdPpn', TextType::class, ['label' => 'Préconisation TD', 'help' => 'A titre indicatif pour les départements.']) //, 'attr' => ['x-model' => 'tdPpn']
            ->add('cmPpn', TextType::class, ['label' => 'Préconisation CM', 'help' => 'A titre indicatif pour les départements.'])//, 'attr' => ['x-model' => 'cmPpn']
            ->add('tpPpn', TextType::class, ['label' => 'Préconisation  TP','help' => 'A titre indicatif pour les départements.'])//, 'attr' => ['x-model' => 'tpPpn']
            ->add('projetPpn', TextType::class, ['label' => 'Préconisation "projet tutoré"','help' => 'A titre indicatif pour les départements.'])
            ->add('livrables', TextareaType::class,
                [
                    'label' => 'Livrables',
                    'attr' => ['rows' => 20],
                    'required' => false,
                    'help' => 'Il est possible d\'utiliser la syntaxe Markdown dans ce bloc de texte',
                ])
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
            ->add('competences', EntityType::class, [
                'class' => ApcCompetence::class,
                'choice_label' => 'nomCourt',
                'label' => 'Nom court de la compétence',
                'attr' => ['@change' => 'changeCompetence'],
                'expanded' => true,
                'multiple' => true,
                'query_builder' => function(ApcComptenceRepository $apcComptenceRepository) {
                    return $apcComptenceRepository->findByDepartementBuilder($this->departement);
                },
                'help' => 'Ajoutez les compétences couvertes par la SAÉ.',
            ])
            ->add('exemples', TextareaType::class,
                [
                    'label' => 'exemples',
                    'attr' => ['rows' => 20],
                    'required' => false,
                    'help' => 'Il est possible d\'utiliser la syntaxe Markdown dans ce bloc de texte',
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
