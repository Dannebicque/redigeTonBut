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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcRessourceType extends AbstractType
{
    protected ?Departement $departement;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->departement = $options['departement'];

        $builder
            ->add('codeMatiere', TextType::class, ['label' => 'Code Ressource'])
            ->add('libelle', TextType::class, ['label' => 'Libellé'])
            ->add('libelleCourt', TextType::class, ['label' => 'Libellé court', 'attr' => ['maxlength' => 25], 'required' => false,
                'help' => '25 caractères maximum'])
            ->add('preRequis', TextareaType::class,
                ['label' => 'Pré-requis', 'attr' => ['rows' => 5], 'required' => false])
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
            ->add('tdPpn', TextType::class, ['label' => 'Heures CM/TD'])
            ->add('tpPpn', TextType::class, ['label' => 'Heures TP'])
            ->add('semestre', EntityType::class, [
                'class' => Semestre::class,
                'required' => true,
                'choice_label' => 'display',
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
        ]);
    }
}
