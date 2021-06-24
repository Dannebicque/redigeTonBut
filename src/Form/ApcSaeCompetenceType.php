<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Form/ApcSaeCompetenceType.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 01/03/2021 18:49
 */

namespace App\Form;

use App\Entity\ApcCompetence;
use App\Entity\ApcSaeCompetence;
use App\Entity\Departement;
use App\Repository\ApcComptenceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcSaeCompetenceType extends AbstractType
{
    protected ?Departement $departement;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->departement = $options['departement'];

        $builder
            ->add('competence', EntityType::class, [
                'class' => ApcCompetence::class,
                'choice_label'=> 'nomCourt',
                'query_builder' => function (ApcComptenceRepository $apcComptenceRepository) {
                    return $apcComptenceRepository->findByDepartementBuilder($this->departement);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApcSaeCompetence::class,
            'departement' => null
        ]);
    }
}
