<?php

namespace App\Controller\Admin;

use App\Entity\PdfDocument;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PdfDocumentCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return PdfDocument::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Document PDF')
            ->setEntityLabelInPlural('Documents PDF')
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL]);
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
                    'Prêt' => PdfDocument::STATUS_READY,
                    'En génération' => PdfDocument::STATUS_GENERATING,
                    'Erreur' => PdfDocument::STATUS_ERROR,
                ])
                ->renderAsBadges([
                    PdfDocument::STATUS_READY => 'success',
                    PdfDocument::STATUS_GENERATING => 'warning',
                    PdfDocument::STATUS_ERROR => 'danger',
                ]),
            DateTimeField::new('updatedAt', 'Mis à jour le'),
            DateTimeField::new('invalidatedAt', 'Invalidé le')->hideOnIndex(),
            TextField::new('currentFilePath', 'Fichier courant')->hideOnIndex(),
            TextField::new('currentFileSha256', 'SHA256 fichier')->hideOnIndex(),
            TextField::new('sourceHash', 'Hash source')->hideOnIndex(),
            TextField::new('parametersHash', 'Hash paramètres')->hideOnIndex(),
            TextareaField::new('parameters', 'Paramètres')
                ->hideOnIndex()
                ->formatValue(static function ($value): string {
                    if (!is_array($value)) {
                        return '';
                    }

                    return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }),
        ];
    }

}
