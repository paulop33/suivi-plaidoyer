<?php

namespace App\Controller\Admin;

use App\Entity\ProgressUpdate;
use App\Enum\ImplementationStatus;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class ProgressUpdateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProgressUpdate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Mise à jour de progression')
            ->setEntityLabelInPlural('Mises à jour de progression')
            ->setDefaultSort(['updateDate' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            AssociationField::new('electedList', 'Liste élue')
                ->setRequired(true)
                ->setHelp('Liste élue concernée par cette mise à jour'),
                
            AssociationField::new('commitment', 'Engagement')
                ->setRequired(true)
                ->setHelp('Engagement suivi par cette mise à jour'),
                
            ChoiceField::new('status', 'Statut')
                ->setChoices(ImplementationStatus::getChoices())
                ->setRequired(true)
                ->renderAsBadges([
                    ImplementationStatus::NOT_STARTED->value => 'secondary',
                    ImplementationStatus::IN_PROGRESS->value => 'primary', 
                    ImplementationStatus::PARTIALLY_DONE->value => 'warning',
                    ImplementationStatus::COMPLETED->value => 'success',
                    ImplementationStatus::ABANDONED->value => 'danger',
                    ImplementationStatus::DELAYED->value => 'info',
                ])
                ->formatValue(function ($value, $entity) {
                    return $entity?->getStatus()?->getLabel();
                }),
                
            TextareaField::new('description', 'Description')
                ->setRequired(true)
                ->setHelp('Description détaillée de l\'avancement'),
                
            IntegerField::new('progressPercentage', 'Pourcentage d\'avancement')
                ->setHelp('Pourcentage d\'avancement (0-100%)')
                ->hideOnIndex(),
                
            DateTimeField::new('updateDate', 'Date de mise à jour')
                ->setRequired(true)
                ->setFormat('dd/MM/yyyy HH:mm'),
                
            DateField::new('expectedCompletionDate', 'Date de fin prévue')
                ->hideOnIndex()
                ->setHelp('Date prévue pour la finalisation'),
                
            TextareaField::new('evidence', 'Preuves/Justificatifs')
                ->hideOnIndex()
                ->setHelp('Description des preuves ou justificatifs'),
                
            TextareaField::new('evidenceLinks', 'Liens vers les preuves')
                ->hideOnIndex()
                ->setHelp('URLs vers des documents ou pages web'),
                
            TextareaField::new('challenges', 'Défis rencontrés')
                ->hideOnIndex()
                ->setHelp('Difficultés ou obstacles rencontrés'),
                
            TextareaField::new('nextSteps', 'Prochaines étapes')
                ->hideOnIndex()
                ->setHelp('Actions prévues pour la suite'),
                
            MoneyField::new('budgetAllocated', 'Budget alloué')
                ->setCurrency('EUR')
                ->hideOnIndex()
                ->setHelp('Budget total alloué pour cet engagement'),
                
            MoneyField::new('budgetSpent', 'Budget dépensé')
                ->setCurrency('EUR')
                ->hideOnIndex()
                ->setHelp('Montant déjà dépensé'),
                
            AssociationField::new('updatedBy', 'Mis à jour par')
                ->hideOnForm()
                ->setHelp('Utilisateur qui a effectué cette mise à jour'),
                
            BooleanField::new('isValidated', 'Validé')
                ->setHelp('Indique si cette mise à jour a été validée'),
                
            AssociationField::new('validatedBy', 'Validé par')
                ->hideOnIndex()
                ->hideOnForm()
                ->setHelp('Utilisateur qui a validé cette mise à jour'),
                
            DateTimeField::new('validationDate', 'Date de validation')
                ->hideOnIndex()
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm'),
                
            DateTimeField::new('creationDate', 'Date de création')
                ->hideOnForm()
                ->hideOnIndex()
                ->setFormat('dd/MM/yyyy HH:mm'),
        ];
    }
}
