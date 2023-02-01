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
                'help' => 'bien-être, développement de compétences, construction de son identité, construction de son projet, intégration d\'un projet de société, etc.'
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
                'label' => 'Éléments du contexte qui ont facilité la mise en œuvre de la SAÉ (opportunités)',
            ])
            ->add('elementsContextesObstacles',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Éléments du contexte qui ont constitué un obstacle à la mise en œuvre de la SAÉ (menaces)',
            ])
            ->add('swatForce',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Autocritique : éléments positifs (forces)',
            ])
            ->add('swatFaiblesse',TextareaType::class, [
                'attr' => ['rows' => 10],
                'required' => false,
                'label' => 'Autocritique : éléments négatifs (faiblesses)',
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
