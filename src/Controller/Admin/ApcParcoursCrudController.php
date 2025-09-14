<?php

namespace App\Controller\Admin;

use App\Entity\ApcParcours;
use App\Entity\Departement;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class ApcParcoursCrudController extends BaseCrudController
{
    public function __construct(private Security $security)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ApcParcours::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('departement')
                ->setLabel('Département')
                ->setCrudController(Departement::class)
                ->setRequired(true)
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    $user = $this->security->getUser();
                    $qb->from(Departement::class, 'd')->select('d');
                    if ($this->isGranted('ROLE_ADMIN')) {
                        return $qb->orderBy('d.libelle', 'ASC');
                    }
                    return $qb
                        ->join('d.cpns', 'c')
                        ->where(':user MEMBER OF d.cpns')
                        ->setParameter('user', $user)
                        ->orderBy('d.libelle', 'ASC');
                }),
            TextField::new('libelle', 'Libellé'),
            TextField::new('code', 'Sigle'),
            ChoiceField::new('couleur', 'Couleur du parcours')
                ->setHelp('Pour l\'affichage des parcours dans les maquettes')
                ->setChoices([
                        'Gris' => 'p1',
                        'Rose' => 'p2',
                        'Violet' => 'p3',
                        'Marron' => 'p4',
                        'Turquoise' => 'p5',
                        'Bleu' => 'p6',
                    ]
                ),
            TextareaField::new('textePresentation')->setLabel('Description')->hideOnIndex()
                ->setHelp('Objectifs du parcours, métiers et secteurs d’activités visés, compétences visées. Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>')->setNumOfRows(30),
            TextareaField::new('modalitesParticulieres')->setLabel('Modalités particulières')->hideOnIndex()
                ->setHelp('Dispositions particulières professions règlementées, certifications spéciales, TP sécurité́. Il est possible d\'utiliser <a href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalMarkdown">la syntaxe Markdown dans ce bloc de texte</a>')->setNumOfRows(30),

        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Parcours')
            ->setEntityLabelInPlural('Parcours')
            ->setDefaultSort(['departement' => 'ASC', 'libelle' => 'ASC'])
            ->setSearchFields(['libelle', 'sigle']);
    }

    public function createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters): QueryBuilder
    {
        $user = $this->security->getUser();
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isGranted('ROLE_ADMIN')) {
            $qb->join('entity.departement', 'd')
                ->andWhere(':user MEMBER OF d.cpns') // Vérifie si l'utilisateur est lié au département
                ->setParameter('user', $user);
        }

        return $qb;
    }
}
