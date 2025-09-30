<?php

namespace App\Controller\Admin;

use App\Entity\Specificity;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class SpecificityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Specificity::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextField::new('slug', 'Slug')->hideOnForm(),
            TextareaField::new('description', 'Description'),
            AssociationField::new('cities', 'Villes')
                ->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    return $entity->getCities()->count() . ' ville(s)';
                }),
            AssociationField::new('specificExpectations', 'Attentes spécifiques')
                ->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    return $entity->getSpecificExpectations()->count() . ' attente(s)';
                }),
        ];
    }
}

