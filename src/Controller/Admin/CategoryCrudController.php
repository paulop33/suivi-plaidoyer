<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextareaField::new('description', 'Description'),
            TextField::new('image', 'URL de l\'image'),
            IntegerField::new('position', 'Ordre d\'affichage')
                ->setHelp('Ordre d\'affichage des catégories (plus petit = affiché en premier)'),
            AssociationField::new('propositions', 'Propositions')
                ->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    return $entity->getPropositions()->count() . ' proposition(s)';
                }),
        ];
    }
}
