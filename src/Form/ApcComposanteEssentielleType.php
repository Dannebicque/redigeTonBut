<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Form/ApcComposanteEssentielleType.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 10:59
 */

namespace App\Form;

use App\Entity\ApcComposanteEssentielle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcComposanteEssentielleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ordre', IntegerType::class, ['label' => 'Ordre de la composante essentielle', 'help' => 'L\'ordre permet de déterminer le code de la composante essentielle.'])
            ->add('code', TextType::class, ['label' => 'Code de la composante essentielle', 'disabled' => true])
            ->add('libelle', TextType::class, ['label' => 'Libellé de la composante essentielle'])
           ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApcComposanteEssentielle::class,
        ]);
    }
}
