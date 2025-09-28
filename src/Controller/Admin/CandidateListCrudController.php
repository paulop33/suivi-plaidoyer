<?php

namespace App\Controller\Admin;

use App\Entity\CandidateList;
use App\Entity\Commitment;
use App\Entity\Proposition;
use App\Enum\CommitmentStatus;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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
        $batchCommitment = Action::new('batchCommitment', 'Gérer les engagements')
            ->linkToCrudAction('batchCommitment')
            ->setIcon('fa fa-check-double')
            ->displayAsButton()
            ->setCssClass('btn btn-success');

        return $actions
            ->add(Crud::PAGE_DETAIL, $batchCommitment)
            ->add(Crud::PAGE_INDEX, $batchCommitment)
            ;
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
        $globalComment = trim($request->request->get('global_comment', ''));
        $propositionComments = $request->request->all('proposition_comments') ?? [];
        $propositionStatuses = $request->request->all('proposition_status') ?? [];

        // Validation de la longueur du commentaire global
        if (strlen($globalComment) > 1000) {
            $this->addFlash('error', 'Le commentaire global ne peut pas dépasser 1000 caractères.');
            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('batchCommitment')
                ->setEntityId($candidateList->getId())
                ->generateUrl());
        }

        // Validation de la longueur des commentaires par proposition
        foreach ($propositionComments as $propositionId => $comment) {
            if (strlen(trim($comment)) > 1000) {
                $this->addFlash('error', 'Le commentaire pour la proposition ne peut pas dépasser 1000 caractères.');
                return $this->redirect($this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('batchCommitment')
                    ->setEntityId($candidateList->getId())
                    ->generateUrl());
            }
        }

        $createdCount = 0;
        $updatedCount = 0;
        $deletedCount = 0;
        $errorCount = 0;

        try {
            $this->entityManager->beginTransaction();

            // Mettre à jour le commentaire global de la liste
            if ($candidateList->getGlobalComment() !== $globalComment) {
                $candidateList->setGlobalComment($globalComment ?: null);
                $this->entityManager->persist($candidateList);
            }

            // Récupérer tous les engagements existants pour cette liste
            $existingCommitments = [];
            foreach ($candidateList->getCommitments() as $commitment) {
                $existingCommitments[$commitment->getProposition()->getId()] = $commitment;
            }

            // Traiter toutes les propositions qui ont un statut défini
            foreach ($propositionStatuses as $propositionId => $propositionStatus) {
                try {
                    $proposition = $this->entityManager->getRepository(Proposition::class)->find($propositionId);
                    if (!$proposition) {
                        $errorCount++;
                        continue;
                    }

                    // Récupérer le commentaire spécifique à cette proposition
                    $propositionComment = isset($propositionComments[$propositionId]) ? trim($propositionComments[$propositionId]) : '';

                    // Convertir le statut en énumération (ou null si aucun statut)
                    $status = match($propositionStatus) {
                        'accepted' => CommitmentStatus::ACCEPTED,
                        'refused' => CommitmentStatus::REFUSED,
                        default => null
                    };

                    if (isset($existingCommitments[$propositionId])) {
                        // Mettre à jour l'engagement existant
                        $existingCommitment = $existingCommitments[$propositionId];
                        $hasChanges = false;

                        if ($existingCommitment->getCommentCandidateList() !== $propositionComment) {
                            $existingCommitment->setCommentCandidateList($propositionComment ?: null);
                            $hasChanges = true;
                        }

                        if ($existingCommitment->getStatus() !== $status) {
                            $existingCommitment->setStatus($status);
                            $hasChanges = true;
                        }

                        if ($hasChanges) {
                            $existingCommitment->setUpdateDate(new \DateTime());
                            $updatedCount++;
                        }

                        // Retirer de la liste pour ne pas le supprimer plus tard
                        unset($existingCommitments[$propositionId]);
                    } else {
                        // Créer un nouvel engagement seulement si un statut est défini
                        if ($status !== null) {
                            $commitment = new Commitment();
                            $commitment->setCandidateList($candidateList);
                            $commitment->setProposition($proposition);
                            $commitment->setCommentCandidateList($propositionComment ?: null);
                            $commitment->setStatus($status);
                            $commitment->setCreationDate(new \DateTime());
                            $commitment->setUpdateDate(new \DateTime());

                            $this->entityManager->persist($commitment);
                            $createdCount++;
                        }
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    // Log l'erreur si nécessaire
                    error_log('Erreur lors de la création de l\'engagement pour la proposition ' . $propositionId . ': ' . $e->getMessage());
                }
            }

            // Supprimer les engagements qui n'ont pas été traités dans cette session
            foreach ($existingCommitments as $commitmentToDelete) {
                $this->entityManager->remove($commitmentToDelete);
                $deletedCount++;
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            // Messages de succès et d'erreur
            if ($createdCount > 0 || $updatedCount > 0 || $deletedCount > 0) {
                $message = sprintf(
                    '%d engagement(s) créé(s), %d mis à jour et %d supprimé(s) pour la liste "%s"',
                    $createdCount,
                    $updatedCount,
                    $deletedCount,
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
