<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Form/ApcParcoursType.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 01/06/2021 16:25
 */

namespace App\Form;

use App\Entity\ApcParcours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcParcoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, ['label' => 'Libellé long', 'disabled' => $options['referentiel_bloque']])
            ->add('code', TextType::class, ['label' => 'Code/Sigle'])
            ->add('textePresentation', TextareaType::class,
                ['label' => 'Texte descriptif du parcours',
                    'help' => 'Objectifs du parcours, métiers et secteurs d’activités visés, compétences visées. Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                    'help_html' => true,
                    'required' => false,
                    'attr' => ['rows' => 50]])
            ->add('modalitesParticulieres', TextareaType::class,
                ['label' => 'Dispositions particulières',
                    'help' => 'Dispositions particulières professions règlementées, certifications spéciales, TP sécurité́. Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                    'help_html' => true,
                    'required' => false,
                    'attr' => ['rows' => 20]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApcParcours::class,
            'referentiel_bloque'=> false
        ]);
    }
}
