<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Form/ApcCompetenceType.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 11/03/2021 17:26
 */

namespace App\Form;

use App\Entity\ApcCompetence;
use App\Entity\Departement;
use App\Form\Type\CollectionStimulusType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class ApcCompetenceType extends AbstractType
{
    protected Departement $departement;
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->departement = $options['departement'];

        $builder
            ->add('nom_court', TextType::class, ['help' => 'Mot désignant la compétence. 50 caractères maximum.', 'attr' => ['maxlength' => 50]])
            ->add('libelle', TextType::class, ['help' => 'Libellé long de la compétence']);
        if (!$options['new']) {
            $builder->add('couleur', ChoiceType::class, [
                'choices' => [
                    '1' => 'c1',
                    '2' => 'c2',
                    '3' => 'c3',
                    '4' => 'c4',
                    '5' => 'c5',
                    '6' => 'c6',
                ],
                'expanded' => true,
                'label' => 'Ordre de la compétence',
                'help' => 'Si une compétence occupe déjà la place elles seront inversées'
            ]);
        }

        if ($options['new']) {
            $builder->add('apcNiveaux', CollectionStimulusType::class, [
                'entry_type' => ApcNiveauType::class,
                'entry_options' => ['label' => false, 'departement' => $this->departement],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'constraints' => [
                    new Count(['min' => 1, 'minMessage' => 'Vous devez saisir au moins un niveau.']),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApcCompetence::class,
            'new' => false,
            'departement' => null,
        ]);
    }
}
