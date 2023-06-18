<?php

namespace App\Form;

use App\Entity\QapesSae;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QapesSaePart4Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('effetsObserves',TextareaType::class, [
                'attr' => ['rows' => 10],
                'label' => 'Effets et résultats observés',
                'required' => false,
                'help' => 'Les effets d’une formation sont multiples. Pour apprécier la qualité de la formation, on va bien entendu prêter attention aux apprentissages effectifs, à leur caractère pérenne, aux usages que les étudiants en ont dans d’autres situations, à la motivation des étudiants traduite en engagement comportemental (participation, persévérance) et engagement cognitif (ne se contente pas d’appliquer sans comprendre), à leurs soucis de l’autre, du travail en équipe, etc. Des données de perception peuvent par exemple être récoltées à l’aide d’un questionnaire d\'avis ou d’un carnet de terrain.'
            ])
            ->add('lienRepertoire',TextareaType::class, [
                'attr' => ['rows' => 4],
                'required' => false,
                'label' => 'Lien vers répertoire en ligne reprenant des documents relatifs aux résultats/effets',
            ])

            ->add('isCoordinationIntervenant', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label' => 'Les intervenants sont bien coordonnés (enseignants, tuteurs, vacataires) et bien au fait de l\'APC et de leur rôle dans la SAE',
            ])
            ->add('coordinationIntervenant',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Commentaire coordination des intervenants',
                'help' => 'Précisez les modalités de coordination des enseignants, les problèmes rencontrés, les solutions mises en œuvre pour y pallier, etc.'
            ])
            ->add('lienDocumentCoordination',TextareaType::class, [
                'attr' => ['rows' => 4],
                'required' => false,
                'label' => 'Lien vers répertoire en ligne reprenant des documents relatifs à la coordination des intervenants',
            ])
            ->add('consignesCommuniquees',ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label' => 'Les compétences visées, consignes et critères d\'évaluation ont été communiqués aux étudiants'])
            ->add('lienConsignes',TextareaType::class, [
                'attr' => ['rows' => 4],
                'required' => false,
                'label' => 'Lien vers répertoire en ligne reprenant des documents relatifs aux consignes communiquées aux étudiants',
            ])
            ->add('elementsContexte',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Éléments du contexte qui ont facilité la mise en œuvre de la SAÉ ',
            ])
            ->add('elementsContextesObstacles',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Éléments du contexte qui ont constitué un obstacle à la mise en œuvre de la SAÉ',
            ])
            ->add('swatForce',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Autocritique : éléments positifs de la SAÉ ',
            ])
            ->add('swatFaiblesse',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Autocritique : éléments négatifs de la SAÉ ',
            ])
            ->add('modificationsApportees',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Modification que vous comptez apporter à la SAE sur base de vos observations.',
            ])
            ->add('temoignagesEtudiants',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Témoignages étudiants',
                'help' => 'Lien(s) et/ou texte'
            ])
            ->add('temoignagesEnseignants',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Témoignages enseignants',
                'help' => 'Lien(s) et/ou texte'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QapesSae::class,
        ]);
    }
}
