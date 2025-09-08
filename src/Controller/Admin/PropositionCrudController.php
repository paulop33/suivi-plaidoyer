<?php

namespace App\Controller\Admin;

use App\Entity\Proposition;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class PropositionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Proposition::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            IntegerField::new('bareme', 'Bareme'),
            AssociationField::new('category', 'Category'),
        ];
    }
}
