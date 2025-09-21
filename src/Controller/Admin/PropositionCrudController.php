<?php

namespace App\Controller\Admin;

use App\Entity\Proposition;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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
        parent::configureFields($pageName);
        return [
            IdField::new('id')->hideOnForm(),
            TextareaField::new('title', 'Titre'),
            TextareaField::new('description', 'Description')->hideOnIndex(),
            IntegerField::new('bareme', 'Bareme'),
            AssociationField::new('category', 'Catégorie'),
        ];
    }
}
