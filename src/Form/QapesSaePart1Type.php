<?php

namespace App\Form;

use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Entity\Iut;
use App\Entity\IutSite;
use App\Entity\IutSiteParcours;
use App\Entity\QapesSae;
use App\Entity\Semestre;
use App\Entity\User;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QapesSaePart1Type extends AbstractType
{
    private bool $edit;
    private ?IutSite $iutSite;
    private ?ApcParcours $apcParcours;
    private ?Departement $departement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->edit = $options['edit'];
        $this->iutSite = $builder->getData()->getIutSite();
        $this->apcParcours = $builder->getData()->getParcours();
        $this->departement = $builder->getData()->getSpecialite();
        $builder
            ->add('auteur', EntityType::class, [
                'class' => User::class,
                'query_builder' => function(UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC')
                        ->addOrderBy('u.prenom', 'ASC');
                },
                'multiple' => true,
                'choice_label' => 'display',
                'label' => 'Auteur(s) de la fiche qualité SAE',
                'help' => 'En complément de l\'auteur initial',
            ])
            ->add('iut', EntityType::class, [
                'class' => Iut::class,
                'choice_label' => 'libelle',
                'label' => 'IUT',
                'mapped' => false,
                'attr' => ['data-action' => 'change->qapes#changeIut']
            ])
            ->add('iutSite', EntityType::class, [
                'class' => IutSite::class,
                'choice_label' => 'libelle',
                'label' => 'Site de l\'IUT',
                'required' => false,
                'attr' => ['data-action' => 'change->qapes#changeSiteIut'],
                'disabled' => !$this->edit,
            ])
            ->add('specialite', EntityType::class, [
                'class' => Departement::class,
                'choice_label' => 'libelle',
                'label' => 'Spécialité',
                'required' => false,
                'disabled' => !$this->edit,
                'attr' => ['data-action' => 'change->qapes#changeSpecialite'],
            ])
            ->add('parcours', EntityType::class, [
                'class' => ApcParcours::class,
                'query_builder' => function(ApcParcoursRepository $er) {
                    if ($this->edit === true) {
                        return $er->createQueryBuilder('p')
                            ->innerJoin(IutSiteParcours::class, 'isp', 'WITH', 'p.id = isp.parcours')
                            ->where('isp.site = :site')
                            ->setParameter('site', $this->iutSite)
                            ->orderBy('p.libelle', 'ASC');
                    } else {
                        return $er->createQueryBuilder('p')
                            ->orderBy('p.libelle', 'ASC');
                    }
                },
                'choice_label' => 'libelle',
                'label' => 'Parcours',
                'required' => false,
                'attr' => ['data-action' => 'change->qapes#changeParcours'],
                'disabled' => !$this->edit,
            ]);
        if ($this->edit === true) {
            $builder->add('sae', EntityType::class, [
                'class' => ApcSae::class,
                'query_builder' => function(ApcSaeRepository $er) {
                    if ($this->apcParcours !== null) {
                        $er->createQueryBuilder('r')
                            ->join('r.apcSaeParcours', 'p')
                            ->where('p.parcours = :parcours')
                            ->setParameter('parcours', $this->apcParcours)
                            ->orderBy('r.codeMatiere', 'ASC')
                            ->addOrderBy('r.libelle', 'ASC');
                    }

                    return $er->createQueryBuilder('r')
                        ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
                        ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
                        ->where('a.departement = :departement')
                        ->andWhere('r.ficheAdaptationLocale = false')
                        ->setParameter('departement', $this->departement->getId())
                        ->orderBy('r.ordre', 'ASC')
                        ->addOrderBy('r.codeMatiere', 'ASC')
                        ->addOrderBy('r.libelle', 'ASC');
                },
                'label' => 'SAE',
                'required' => false,
                'disabled' => !$this->edit,
            ]);
        } else {
            $builder->add('sae', ChoiceType::class, [
                'choices' => [],
                'label' => 'SAE',
                'required' => false,
                'disabled' => !$this->edit,
            ]);
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QapesSae::class,
            'edit' => false
        ]);
    }
}
