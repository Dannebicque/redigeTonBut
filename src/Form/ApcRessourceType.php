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
use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
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

class ApcRessourceType extends AbstractType
{
    protected ?Departement $departement;
    protected bool $editable;
    protected bool $verouille_croise;
    protected ?ApcParcours $parcours;


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->departement = $options['departement'];
        $this->editable = !$options['editable'];
        $this->parcours = $options['parcours'];
        $this->verouille_croise = $options['verouille_croise'];

        $builder
            ->add('codeMatiere', TextType::class, ['label' => 'Code Ressource', 'disabled' => $this->editable, 'help' => 'Code généré automatiquement'])
            ->add('ficheAdaptationLocale', ChoiceType::class, ['label' => 'Fiche d\'adaptation locale ?', 'expanded' => true, 'choices' => ['Oui' => true, 'Non' => false,], 'attr' => ['class' => 'text-white'], 'label_attr' => ['class' => 'text-white'],'help' => 'Si la fiche est de l\'adaptation locale, elle ne sera pas prise en compte dans les tableaux et simplement affichées aux collègues', 'help_attr' => ['class' => 'text-white']])
            ->add('libelle', TextType::class, ['label' => 'Libellé', 'disabled' => $this->verouille_croise])
            ->add('ordre', NumberType::class, ['label' => 'Ordre dans le semestre', 'disabled' => $this->verouille_croise,])
            ->add('libelleCourt', TextType::class, ['label' => 'Libellé court', 'attr' => ['maxlength' => 25], 'required' => false,
                'help' => '25 caractères maximum, utile pour Apogée', 'disabled' => $this->verouille_croise])
            ->add('description', TextareaType::class,
                [
                    'attr' => ['rows' => 20],
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>',
                    'help_html' => true,
                ])
            ->add('motsCles', TextType::class,
                [
                    'label' => 'Mots clés',
                    'help' => 'Utilisez le "," pour séparer les mots clés.',
                    'required' => false,
                ])
            ->add('heuresTotales', TextType::class, ['label' => 'Heures totales', ])
            ->add('tpPpn', TextType::class, ['label' => 'Dont heures TP',])
            ->add('cmPreco', TextType::class, ['label' => 'Préconisation Heures CM',
                'required' => false,
                'help' => 'A titre indicatif pour les départements.',
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])
            ->add('tdPreco', TextType::class, ['label' => 'Préconisation Heures TD',
                'required' => false,
                'help' => 'A titre indicatif pour les départements.',
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])
            ->add('tpPreco', TextType::class, ['label' => 'Préconisation Heures TP',
                'required' => false,
                'help' => 'A titre indicatif pour les départements.',
                'label_attr' => ['class' => 'text-white'],
                'help_attr' => ['class' => 'text-white'],])

            ->add('semestre', EntityType::class, [
                'class' => Semestre::class,
                'required' => true, 'disabled' => $this->verouille_croise,
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
            'data_class' => ApcRessource::class,
            'departement' => null,
            'editable' => null,
            'verouille_croise' => null,
            'parcours' => null,
        ]);
    }
}
