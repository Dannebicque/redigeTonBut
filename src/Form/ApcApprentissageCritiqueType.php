<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Form/ApcApprentissageCritiqueType.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 10:59
 */

namespace App\Form;

use App\Entity\ApcApprentissageCritique;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcApprentissageCritiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, ['disabled' => true, 'help'  => 'Le code est généré automatiquement en fonction de l\'ordre.'])
            ->add('ordre', IntegerType::class, ['label' => 'Ordre de l\'AC',
                'help'  => 'Si un AC occupe déjà la place ils seront inversés',
            ])
            ->add('libelle', TextType::class, ['label' => 'Libellé de l\'AC']); // ['disabled' => true]
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApcApprentissageCritique::class,
        ]);
    }
}
