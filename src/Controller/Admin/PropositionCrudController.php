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
            TextField::new('image', 'URL de l\'image')->hideOnIndex(),
            IntegerField::new('bareme', 'Bareme'),
            IntegerField::new('position', 'Ordre d\'affichage')
                ->setHelp('Ordre d\'affichage des propositions dans la catégorie (plus petit = affiché en premier)'),
            AssociationField::new('category', 'Catégorie'),
            TextareaField::new('commonExpectation', 'Attente commune')
                ->setHelp('L\'attente commune pour toutes les mairies. Si vide, seules les attentes spécifiques s\'appliquent.')
                ->hideOnIndex(),
            AssociationField::new('specificExpectations', 'Attentes spécifiques')
                ->setHelp('Pour gérer les attentes spécifiques, utilisez le menu "Attentes spécifiques"')
                ->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    return $entity->getSpecificExpectations()->count() . ' attente(s) spécifique(s)';
                }),
        ];
    }
}
