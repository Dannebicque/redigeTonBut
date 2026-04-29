<?php

namespace App\Controller\Admin;

use App\Entity\PdfJob;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class PdfJobCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return PdfJob::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Job PDF')
            ->setEntityLabelInPlural('Jobs PDF')
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::DELETE]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('sourceType', 'Type source'),
            TextField::new('sourceId', 'ID source'),
            TextField::new('documentKey', 'Document'),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En attente' => PdfJob::STATUS_QUEUED,
                    'Succès' => PdfJob::STATUS_SUCCESS,
                    'Erreur' => PdfJob::STATUS_ERROR,
                ])
                ->renderAsBadges([
                    PdfJob::STATUS_QUEUED => 'warning',
                    PdfJob::STATUS_SUCCESS => 'success',
                    PdfJob::STATUS_ERROR => 'danger',
                ]),
            UrlField::new('resultTempUrl', 'URL résultat')
                ->hideOnIndex()
                ->setRequired(false),
            TextField::new('sourceHash', 'Hash source')->hideOnIndex(),
            TextareaField::new('errorMessage', 'Erreur')->hideOnIndex()->setRequired(false),
            TextareaField::new('logs', 'Logs')->hideOnIndex()->setRequired(false),
        ];
    }
}
