<?php

namespace App\Controller\Admin;

use App\Entity\ElectedList;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class ElectedListCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ElectedList::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            AssociationField::new('city', 'Ville')
                ->setRequired(true)
                ->setHelp('Ville où cette liste a été élue'),
                
            AssociationField::new('candidateList', 'Liste candidate')
                ->setRequired(true)
                ->setHelp('Liste candidate qui a été élue'),
                
            TextField::new('mayorName', 'Nom du maire')
                ->setRequired(true)
                ->setHelp('Nom complet du maire élu'),
                
            DateField::new('electionDate', 'Date d\'élection')
                ->setRequired(true)
                ->setHelp('Date des élections municipales'),
                
            IntegerField::new('mandateStartYear', 'Début du mandat')
                ->setRequired(true)
                ->setHelp('Année de début du mandat (ex: 2026)'),
                
            IntegerField::new('mandateEndYear', 'Fin du mandat')
                ->setRequired(true)
                ->setHelp('Année de fin du mandat (ex: 2032)'),
                
            EmailField::new('contactEmail', 'Email de contact')
                ->hideOnIndex()
                ->setHelp('Email de contact de la mairie'),
                
            TelephoneField::new('contactPhone', 'Téléphone')
                ->hideOnIndex()
                ->setHelp('Numéro de téléphone de la mairie'),
                
            TextareaField::new('programSummary', 'Résumé du programme')
                ->hideOnIndex()
                ->setHelp('Résumé du programme électoral de la liste élue'),
                
            BooleanField::new('isActive', 'Actif')
                ->setHelp('Indique si cette liste est actuellement en fonction'),
                
            DateTimeField::new('creationDate', 'Date de création')
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm'),
                
            DateTimeField::new('updateDate', 'Dernière modification')
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm'),
        ];
    }
}
