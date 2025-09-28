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


    public function configureActions(Actions $actions): Actions
    {
        $batchAdd = Action::new('batchAdd', 'Batch Add')
            ->linkToCrudAction('batchAdd')
            ->createAsGlobalAction()
            ->setIcon('fa fa-plus')
        ;

        return $actions->add(Crud::PAGE_INDEX, $batchAdd);
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

    public function batchAdd(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $names = array_filter(array_map('trim', explode("\n", $request->request->get('names'))));

            foreach ($names as $name) {
                $product = new Commitment();
                $product->setName($name);
                $em->persist($product);
            }
            $em->flush();

            $this->addFlash('success', count($names) . ' produits ajoutés avec succès');

            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => self::class,
                'crudAction' => 'index',
            ]));
        }

        return $this->render('admin/batch_add.html.twig');
    }
}
