<?php

namespace App\Controller\Admin;

use App\Entity\CandidateList;
use App\Entity\Commitment;
use App\Entity\Proposition;
use App\Repository\PropositionRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CandidateListCrudController extends AbstractCrudController
{
    public function __construct(
        private PropositionRepository $propositionRepository,
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return CandidateList::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $batchCommitment = Action::new('batchCommitment', 'Engager sur toutes les propositions')
            ->linkToCrudAction('batchCommitment')
            ->setIcon('fa fa-check-double')
            ->displayAsButton()
            ->setCssClass('btn btn-success')
            ->displayIf(fn ($entity) => $entity instanceof CandidateList);

        return $actions
            ->add(Crud::PAGE_DETAIL, $batchCommitment)
            ->add(Crud::PAGE_INDEX, $batchCommitment);
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

    public function batchCommitment(AdminContext $context): Response
    {
        $request = $context->getRequest();
        $candidateListId = $request->query->get('entityId');

        if (!$candidateListId) {
            $this->addFlash('error', 'ID de la liste candidate manquant');
            return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
        }

        $candidateList = $this->entityManager->getRepository(CandidateList::class)->find($candidateListId);

        if (!$candidateList) {
            $this->addFlash('error', 'Liste candidate non trouvée');
            return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
        }

        if ($request->isMethod('POST')) {
            return $this->processBatchCommitment($request, $candidateList, $context);
        }

        // Récupérer toutes les propositions
        $propositions = $this->propositionRepository->findAllWithCommitments();

        // Récupérer les engagements existants pour cette liste
        $existingCommitments = [];
        foreach ($candidateList->getCommitments() as $commitment) {
            $existingCommitments[$commitment->getProposition()->getId()] = $commitment;
        }

        return $this->render('admin/batch_commitment.html.twig', [
            'candidateList' => $candidateList,
            'propositions' => $propositions,
            'existingCommitments' => $existingCommitments,
        ]);
    }

    private function processBatchCommitment(Request $request, CandidateList $candidateList, AdminContext $context): Response
    {
        $selectedPropositions = $request->request->all('propositions') ?? [];
        $globalComment = trim($request->request->get('global_comment', ''));

        // Validation
        if (empty($selectedPropositions)) {
            $this->addFlash('error', 'Veuillez sélectionner au moins une proposition.');
            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('batchCommitment')
                ->setEntityId($candidateList->getId())
                ->generateUrl());
        }

        // Validation de la longueur du commentaire
        if (strlen($globalComment) > 1000) {
            $this->addFlash('error', 'Le commentaire ne peut pas dépasser 1000 caractères.');
            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('batchCommitment')
                ->setEntityId($candidateList->getId())
                ->generateUrl());
        }

        $createdCount = 0;
        $updatedCount = 0;
        $errorCount = 0;

        try {
            $this->entityManager->beginTransaction();

            foreach ($selectedPropositions as $propositionId) {
                try {
                    $proposition = $this->entityManager->getRepository(Proposition::class)->find($propositionId);
                    if (!$proposition) {
                        $errorCount++;
                        continue;
                    }

                    // Vérifier si un engagement existe déjà
                    $existingCommitment = $this->entityManager->getRepository(Commitment::class)
                        ->findOneBy([
                            'candidateList' => $candidateList,
                            'proposition' => $proposition
                        ]);

                    if ($existingCommitment) {
                        // Mettre à jour le commentaire si fourni ou différent
                        if (!empty($globalComment) && $existingCommitment->getCommentCandidateList() !== $globalComment) {
                            $existingCommitment->setCommentCandidateList($globalComment);
                            $existingCommitment->setUpdateDate(new \DateTime());
                            $updatedCount++;
                        }
                    } else {
                        // Créer un nouvel engagement
                        $commitment = new Commitment();
                        $commitment->setCandidateList($candidateList);
                        $commitment->setProposition($proposition);
                        $commitment->setCommentCandidateList($globalComment);
                        $commitment->setCreationDate(new \DateTime());
                        $commitment->setUpdateDate(new \DateTime());

                        $this->entityManager->persist($commitment);
                        $createdCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    // Log l'erreur si nécessaire
                    error_log('Erreur lors de la création de l\'engagement pour la proposition ' . $propositionId . ': ' . $e->getMessage());
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            // Messages de succès et d'erreur
            if ($createdCount > 0 || $updatedCount > 0) {
                $message = sprintf(
                    '%d engagement(s) créé(s) et %d mis à jour pour la liste "%s"',
                    $createdCount,
                    $updatedCount,
                    $candidateList->getNameList()
                );
                $this->addFlash('success', $message);
            }

            if ($errorCount > 0) {
                $this->addFlash('warning', sprintf(
                    '%d erreur(s) rencontrée(s) lors du traitement. Certains engagements n\'ont pas pu être créés.',
                    $errorCount
                ));
            }

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement des engagements. Veuillez réessayer.');
            error_log('Erreur lors du batch commitment: ' . $e->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($candidateList->getId())
            ->generateUrl());
    }
}
