<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Form/ApcRessourceType.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 31/05/2021 12:34
 */

namespace App\Form;

use App\Entity\ApcCompetence;
use App\Entity\ApcRessource;
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

class ApcRessourceType extends AbstractType
{
    protected ?Departement $departement;
    protected bool $editable;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->departement = $options['departement'];
        $this->editable = $options['editable'];

        $builder
            ->add('codeMatiere', TextType::class, ['label' => 'Code Ressource', 'disabled' => $this->editable, 'help' => 'Code généré automatiquement'])
            ->add('libelle', TextType::class, ['label' => 'Libellé'])
            ->add('ordre', NumberType::class, ['label' => 'Ordre dans le semestre'])
            ->add('libelleCourt', TextType::class, ['label' => 'Libellé court', 'attr' => ['maxlength' => 25], 'required' => false,
                'help' => '25 caractères maximum'])
//            ->add('preRequis', TextareaType::class,
//                ['label' => 'Pré-requis', 'attr' => ['rows' => 5], 'required' => false])
            ->add('description', TextareaType::class,
                [
                    'attr' => ['rows' => 20],
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Il est possible d\'utiliser la syntaxe Markdown dans ce bloc de texte'
                ])
            ->add('motsCles', TextType::class,
                [
                    'label' => 'Mots clés',
                    'help' => 'Utilisez le "," pour séparer les mots clés.',
                    'required' => false,
                ])
            ->add('heuresTotales', TextType::class, ['label' => 'Heures totales', ]) //'attr' => ['x-model' => 'heuresTotales']
            ->add('tdPpn', TextType::class, ['label' => 'Préconisation TD', 'help' => 'A titre indicatif pour les départements.', ]) //'attr' => ['x-model' => 'tdPpn']
            ->add('cmPpn', TextType::class, ['label' => 'Préconisation CM', 'help' => 'A titre indicatif pour les départements.', ]) //'attr' => ['x-model' => 'cmPpn']
            ->add('tpPpn', TextType::class, ['label' => 'Dont heures TP',]) // 'attr' => ['x-model' => 'tpPpn']
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
                'help' => 'Ajoutez les compétences couvertes par la ressource.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApcRessource::class,
            'departement' => null,
            'editable' => null,
        ]);
    }
}
