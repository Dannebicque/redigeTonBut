<?php

namespace App\Form;

use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Entity\Iut;
use App\Entity\IutSite;
use App\Entity\QapesSae;
use App\Entity\QapesSaeCritereReponse;
use App\Entity\User;
use App\Repository\QapesSaeCritereReponseRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QapesSaePart3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateEvaluation')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QapesSae::class,
        ]);
    }
}
