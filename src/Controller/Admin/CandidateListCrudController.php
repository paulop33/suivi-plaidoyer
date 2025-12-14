<?php

namespace App\Controller\Admin;

use App\Entity\CandidateList;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CandidateListCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UriSigner $uriSigner
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return CandidateList::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $batchAdd = Action::new('batchAdd', 'Page de saisie des candidat·es')
            ->linkToUrl(function ($entity) {
                // Générer l'URL de base
                $url = $this->generateUrl('public_batch_commitment', [
                    'candidateListId' => $entity->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                // Signer l'URL pour la sécurité
                return $this->uriSigner->sign($url);
            })
            ->setIcon('fa fa-plus')
        ;

        return $actions->add(Crud::PAGE_INDEX, $batchAdd);
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
            TextareaField::new('globalComment', 'Commentaire global'),
        ];
    }

}
