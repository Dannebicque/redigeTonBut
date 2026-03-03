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
use App\Entity\Version;
use App\Repository\ApcComptenceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApcSaeCompetenceType extends AbstractType
{
    protected ?Version $version;

    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $this->version = $options['version'];

        $builder
            ->add('competence', EntityType::class, [
                'class' => ApcCompetence::class,
                'choice_label'=> 'nomCourt',
                'query_builder' => function (ApcComptenceRepository $apcComptenceRepository) {
                    return $apcComptenceRepository->findByVersionBuilder($this->version);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApcSaeCompetence::class,
            'version' => null
        ]);
    }
}
