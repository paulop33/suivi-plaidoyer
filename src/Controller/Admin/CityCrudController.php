<?php

namespace App\Controller\Admin;

use App\Entity\City;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class CityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return City::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextField::new('slug', 'Slug')->hideOnForm(),
            AssociationField::new('specificities', 'Spécificités')
                ->setHelp('Sélectionnez les spécificités de cette ville (intra-rocade, extra-rocade, ville, campagne, etc.)'),
            AssociationField::new('referentesAssociations', 'Associations référentes')
                ->hideOnIndex(),
        ];
    }
}
