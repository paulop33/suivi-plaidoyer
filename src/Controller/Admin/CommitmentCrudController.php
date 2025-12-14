<?php

namespace App\Controller\Admin;

use App\Entity\Commitment;
use App\Enum\CommitmentStatus;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommitmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commitment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        parent::configureFields($pageName);
        return [
            AssociationField::new('candidateList', 'Liste'),
            AssociationField::new('proposition', 'Proposition'),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'Accepté' => CommitmentStatus::ACCEPTED,
                    'Refusé' => CommitmentStatus::REFUSED,
                ])
                ->allowMultipleChoices(false)
                ->renderExpanded(false)
                ->renderAsBadges([
                    CommitmentStatus::ACCEPTED->value => 'success',
                    CommitmentStatus::REFUSED->value => 'danger'
                ])
                ->formatValue(function ($value) {
                    return $value instanceof CommitmentStatus ? $value->getLabel() : $value;
                }),
            TextareaField::new('commentCandidateList', 'Commentaire spécifique'),
        ];
    }
}
