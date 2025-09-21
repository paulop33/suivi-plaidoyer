<?php

namespace App\Controller\Admin;

use App\Entity\CandidateList;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CandidateListCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CandidateList::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nameList', 'Nom de la liste'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom'),
            EmailField::new('email'),
            TextField::new('phone', 'Téléphone'),
            AssociationField::new('city', 'Ville'),
        ];
    }
}
