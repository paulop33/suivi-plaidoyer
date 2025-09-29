<?php

namespace App\Controller;

use App\Entity\Proposition;
use App\Service\CommitmentDataService;
use App\Repository\PropositionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/propositions')]
class PropositionController extends AbstractController
{
    public function __construct(
        private PropositionRepository $propositionRepository,
        private CommitmentDataService $commitmentDataService
    ) {
    }

    #[Route('', name: 'app_propositions_index')]
    public function index(): Response
    {
        // Obtenir les propositions les plus signées
        $mostSignedPropositions = $this->propositionRepository->findMostSignedPropositions(20);

        return $this->render('public/propositions_index.html.twig', [
            'mostSignedPropositions' => $mostSignedPropositions,
            'breadcrumbItems' => [
                [
                    'label' => 'Propositions',
                    'icon' => 'fas fa-lightbulb'
                ]
            ],
            'quickActions' => [
                [
                    'url' => $this->generateUrl('app_home'),
                    'label' => 'Retour à l\'accueil',
                    'icon' => 'fas fa-home',
                    'class' => 'btn-outline-secondary'
                ]
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_proposition_show', requirements: ['id' => '\d+'])]
    public function show(Proposition $proposition): Response
    {
        // Utiliser la méthode optimisée du repository
        $proposition = $this->propositionRepository->findOneWithCommitments($proposition->getId());

        // Organiser toutes les communes avec leurs engagements (même celles sans engagement)
        $commitmentsByCity = $this->commitmentDataService->organizeAllCitiesForProposition($proposition);

        // Obtenir les listes qui ont signé cette proposition
        $signedLists = $this->commitmentDataService->getListsForProposition($proposition);

        return $this->render('public/proposition_show.html.twig', [
            'proposition' => $proposition,
            'commitmentsByCity' => $commitmentsByCity,
            'signedLists' => $signedLists,
            'totalCommitments' => count($proposition->getCommitments()),
            'breadcrumbItems' => [
                [
                    'label' => $proposition->getCategory()->getName(),
                    'url' => $this->generateUrl('app_category_show', ['id' => $proposition->getCategory()->getId(), 'slug' => $proposition->getCategory()->getSlug()]),
                    'icon' => 'fas fa-tag'
                ],
                [
                    'label' => $proposition->getTitle(),
                    'icon' => 'fas fa-lightbulb'
                ]
            ],
            'quickActions' => [
                [
                    'url' => $this->generateUrl('app_category_show', ['id' => $proposition->getCategory()->getId(), 'slug' => $proposition->getCategory()->getSlug()]),
                    'label' => 'Voir la catégorie',
                    'icon' => 'fas fa-tag',
                    'class' => 'btn-outline-primary'
                ]
            ]
        ]);
    }
}
