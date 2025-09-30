<?php

namespace App\Controller\Admin;

use App\Entity\SpecificExpectation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class SpecificExpectationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SpecificExpectation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('proposition', 'Proposition')
                ->setHelp('Sélectionnez la proposition concernée')
                ->setRequired(true),
            AssociationField::new('specificity', 'Spécificité')
                ->setHelp('Sélectionnez le type de territoire (intra-rocade, centre urbain, etc.)')
                ->setRequired(true),
            TextareaField::new('expectation', 'Attente')
                ->setHelp('Décrivez ce qui est attendu des mairies ayant cette spécificité')
                ->setRequired(true)
                ->setFormTypeOption('attr', ['rows' => 5]),
        ];
    }
}

