<?php

namespace App\Controller;

use App\Entity\CandidateList;
use App\Entity\Commitment;
use App\Entity\Proposition;
use App\Enum\CommitmentStatus;
use App\Form\CandidateListPasswordType;
use App\Repository\PropositionRepository;
use App\Service\CandidateListPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicBatchCommitmentController extends AbstractController
{
    public function __construct(
        private PropositionRepository $propositionRepository,
        private EntityManagerInterface $entityManager,
        private UriSigner $uriSigner,
        private CandidateListPasswordService $passwordService
    ) {
    }

    #[Route('/public/batch-commitment/{candidateListId}', name: 'public_batch_commitment', methods: ['GET', 'POST'])]
    public function batchCommitment(int $candidateListId, Request $request): Response
    {
        // Validation du token de sécurité
        $this->validateSecurityToken($request);

        // Récupération de la liste candidate
        $candidateList = $this->entityManager->getRepository(CandidateList::class)->find($candidateListId);

        if (!$candidateList) {
            throw new NotFoundHttpException('Liste candidate non trouvée');
        }

        // Vérifier si un mot de passe est requis
        if ($candidateList->hasPassword()) {
            $session = $request->getSession();
            $sessionKey = 'candidate_list_password_' . $candidateListId;

            // Vérifier si l'utilisateur est déjà authentifié pour cette liste
            if (!$session->get($sessionKey, false)) {
                return $this->handlePasswordAuthentication($request, $candidateList);
            }
        }

        if ($request->isMethod('POST')) {
            return $this->processBatchCommitment($request, $candidateList);
        }

        // Récupérer toutes les propositions
        $propositions = $this->propositionRepository->findAllWithCommitments();

        // Organiser les propositions par catégorie
        $propositionsByCategory = [];
        foreach ($propositions as $proposition) {
            $categoryName = $proposition->getCategory()->getName();
            if (!isset($propositionsByCategory[$categoryName])) {
                $propositionsByCategory[$categoryName] = [];
            }
            $propositionsByCategory[$categoryName][] = $proposition;
        }

        // Récupérer les engagements existants pour cette liste
        $existingCommitments = [];
        foreach ($candidateList->getCommitments() as $commitment) {
            $existingCommitments[$commitment->getProposition()->getId()] = $commitment;
        }

        // Générer l'URL signée pour définir un mot de passe
        $setPasswordUrl = $this->generateUrl('public_batch_commitment_set_password', [
            'candidateListId' => $candidateListId
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        $setPasswordUrl = $this->uriSigner->sign($setPasswordUrl);

        return $this->render('public/batch_commitment.html.twig', [
            'candidateList' => $candidateList,
            'propositionsByCategory' => $propositionsByCategory,
            'existingCommitments' => $existingCommitments,
            'signedUrl' => $request->getUri(), // Passer l'URL signée complète pour les formulaires
            'setPasswordUrl' => $setPasswordUrl, // URL signée pour définir un mot de passe
        ]);
    }

    private function validateSecurityToken(Request $request): void
    {
        // Construire l'URL complète pour UriSigner
        $uri = $request->getUri();

        // Utilise UriSigner pour vérifier la signature de l'URL
        if (!$this->uriSigner->check($uri)) {
            throw new AccessDeniedHttpException('URL non signée ou signature invalide');
        }
    }

    private function processBatchCommitment(Request $request, CandidateList $candidateList): Response
    {
        // Vérifier si c'est une demande de changement de mot de passe
        if ($request->request->get('action') === 'change_password') {
            return $this->handlePasswordChange($request, $candidateList);
        }

        $globalComment = trim($request->request->get('global_comment', ''));
        $propositionComments = $request->request->all('proposition_comments') ?? [];
        $propositionStatuses = $request->request->all('proposition_status') ?? [];

        // Validation de la longueur du commentaire global
        if (strlen($globalComment) > 1000) {
            $this->addFlash('error', 'Le commentaire global ne peut pas dépasser 1000 caractères.');
            $redirectUrl = $this->generateUrl('public_batch_commitment', [
                'candidateListId' => $candidateList->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $signedUrl = $this->uriSigner->sign($redirectUrl);
            return $this->redirect($signedUrl);
        }

        // Validation de la longueur des commentaires par proposition
        foreach ($propositionComments as $propositionId => $comment) {
            if (strlen(trim($comment)) > 1000) {
                $this->addFlash('error', 'Le commentaire pour la proposition ne peut pas dépasser 1000 caractères.');
                $redirectUrl = $this->generateUrl('public_batch_commitment', [
                    'candidateListId' => $candidateList->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL);
                $signedUrl = $this->uriSigner->sign($redirectUrl);
                return $this->redirect($signedUrl);
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

        // Redirection vers l'URL signée originale
        $redirectUrl = $this->generateUrl('public_batch_commitment', [
            'candidateListId' => $candidateList->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        // Re-signer l'URL pour la redirection
        $signedUrl = $this->uriSigner->sign($redirectUrl);

        return $this->redirect($signedUrl);
    }

    private function handlePasswordAuthentication(Request $request, CandidateList $candidateList): Response
    {
        $form = $this->createForm(CandidateListPasswordType::class, null, [
            'is_setting_password' => false
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedPassword = $form->get('password')->getData();

            // Vérifier le mot de passe avec le service de hachage
            if ($this->passwordService->verifyPassword($submittedPassword, $candidateList->getPassword())) {
                // Authentification réussie, stocker en session
                $session = $request->getSession();
                $sessionKey = 'candidate_list_password_' . $candidateList->getId();
                $session->set($sessionKey, true);

                // Rediriger vers la même page pour afficher le formulaire d'engagements
                $redirectUrl = $this->generateUrl('public_batch_commitment', [
                    'candidateListId' => $candidateList->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL);
                $signedUrl = $this->uriSigner->sign($redirectUrl);

                return $this->redirect($signedUrl);
            } else {
                $this->addFlash('error', 'Mot de passe incorrect.');
            }
        }

        return $this->render('public/password_form.html.twig', [
            'candidateList' => $candidateList,
            'form' => $form->createView(),
            'signedUrl' => $request->getUri(),
        ]);
    }

    #[Route('/public/batch-commitment/{candidateListId}/set-password', name: 'public_batch_commitment_set_password', methods: ['GET', 'POST'])]
    public function setPassword(int $candidateListId, Request $request): Response
    {
        // Validation du token de sécurité
        $this->validateSecurityToken($request);

        // Récupération de la liste candidate
        $candidateList = $this->entityManager->getRepository(CandidateList::class)->find($candidateListId);

        if (!$candidateList) {
            throw new NotFoundHttpException('Liste candidate non trouvée');
        }

        // Vérifier que la liste n'a pas déjà un mot de passe
        if ($candidateList->hasPassword()) {
            $this->addFlash('error', 'Cette liste a déjà un mot de passe défini.');
            $redirectUrl = $this->generateUrl('public_batch_commitment', [
                'candidateListId' => $candidateList->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $signedUrl = $this->uriSigner->sign($redirectUrl);
            return $this->redirect($signedUrl);
        }

        $form = $this->createForm(CandidateListPasswordType::class, null, [
            'is_setting_password' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();

            // Hacher et stocker le mot de passe
            $hashedPassword = $this->passwordService->hashPassword($password);
            $candidateList->setPassword($hashedPassword);
            $this->entityManager->persist($candidateList);
            $this->entityManager->flush();

            // Authentifier automatiquement l'utilisateur
            $session = $request->getSession();
            $sessionKey = 'candidate_list_password_' . $candidateList->getId();
            $session->set($sessionKey, true);

            $this->addFlash('success', 'Mot de passe défini avec succès.');

            // Rediriger vers la page d'engagements
            $redirectUrl = $this->generateUrl('public_batch_commitment', [
                'candidateListId' => $candidateList->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $signedUrl = $this->uriSigner->sign($redirectUrl);

            return $this->redirect($signedUrl);
        }

        return $this->render('public/password_form.html.twig', [
            'candidateList' => $candidateList,
            'form' => $form->createView(),
            'signedUrl' => $request->getUri(),
            'is_setting_password' => true,
        ]);
    }

    private function handlePasswordChange(Request $request, CandidateList $candidateList): Response
    {
        $currentPassword = $request->request->get('current_password');
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        // Vérifier le mot de passe actuel
        if (!$this->passwordService->verifyPassword($currentPassword, $candidateList->getPassword())) {
            $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
        } elseif ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
        } elseif (strlen($newPassword) < 8) {
            $this->addFlash('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
        } else {
            // Hacher et mettre à jour le mot de passe
            $hashedPassword = $this->passwordService->hashPassword($newPassword);
            $candidateList->setPassword($hashedPassword);
            $this->entityManager->persist($candidateList);
            $this->entityManager->flush();

            $this->addFlash('success', 'Mot de passe modifié avec succès.');
        }

        // Redirection vers l'URL signée originale
        $redirectUrl = $this->generateUrl('public_batch_commitment', [
            'candidateListId' => $candidateList->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $signedUrl = $this->uriSigner->sign($redirectUrl);
        return $this->redirect($signedUrl);
    }
}
