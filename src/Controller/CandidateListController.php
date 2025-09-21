<?php

namespace App\Controller;

use App\Entity\CandidateList;
use App\Service\CommitmentDataService;
use App\Service\StatisticsService;
use App\Repository\CandidateListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/listes')]
class CandidateListController extends AbstractController
{
    public function __construct(
        private CandidateListRepository $candidateListRepository,
        private CommitmentDataService   $commitmentDataService,
        private StatisticsService       $statisticsService
    ) {
    }

    #[Route('', name: 'app_candidate_lists_index')]
    public function index(): Response
    {
        // Obtenir les listes les plus actives
        $topLists = $this->statisticsService->getTopCandidateListsByEngagements(20);

        return $this->render('public/candidate_lists_index.html.twig', [
            'topLists' => $topLists,
            'breadcrumbItems' => [
                [
                    'label' => 'Listes candidates',
                    'icon' => 'fas fa-users'
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

    #[Route('/{id}/{slug}', name: 'app_candidate_list_show', requirements: ['id' => '\d+'])]
    public function show(int $id, string $slug): Response
    {
        // Find candidate list by id and verify slug
        $candidateList = $this->candidateListRepository->findOneWithCommitments($id);

        if (!$candidateList) {
            throw $this->createNotFoundException('Liste candidate non trouvée');
        }

        // Redirect to correct URL if slug doesn't match
        if ($candidateList->getSlug() !== $slug) {
            return $this->redirectToRoute('app_candidate_list_show', [
                'id' => $candidateList->getId(),
                'slug' => $candidateList->getSlug()
            ], 301);
        }

        // Organiser les engagements par catégorie
        $commitmentsByCategory = $this->commitmentDataService->organizeCommitmentsByCategory($candidateList);

        // Calculer les statistiques de la liste
        $listStats = $this->statisticsService->calculateCandidateListStats($candidateList);

        // Calculer le score d'engagement
        $engagementScore = $this->commitmentDataService->calculateEngagementScore($candidateList);

        return $this->render('public/candidate_list_show.html.twig', [
            'candidateList' => $candidateList,
            'commitmentsByCategory' => $commitmentsByCategory,
            'listStats' => $listStats,
            'engagementScore' => $engagementScore,
            'breadcrumbItems' => [
                [
                    'label' => 'Communes',
                    'url' => $this->generateUrl('app_cities_index'),
                    'icon' => 'fas fa-city'
                ],
                [
                    'label' => $candidateList->getCity()->getName(),
                    'url' => $this->generateUrl('app_city_show', ['slug' => $candidateList->getCity()->getSlug()]),
                    'icon' => 'fas fa-map-marker-alt'
                ],
                [
                    'label' => $candidateList->getNameList(),
                    'icon' => 'fas fa-users'
                ]
            ],
            'quickActions' => [
                [
                    'url' => $this->generateUrl('app_city_show', ['slug' => $candidateList->getCity()->getSlug()]),
                    'label' => 'Voir la commune',
                    'icon' => 'fas fa-city',
                    'class' => 'btn-outline-primary'
                ]
            ]
        ]);
    }

    #[Route('/{id}/stats', name: 'app_candidate_list_stats', requirements: ['id' => '\d+'])]
    public function stats(CandidateList $candidateList): Response
    {
        $listStats = $this->statisticsService->calculateCandidateListStats($candidateList);
        $engagementScore = $this->commitmentDataService->calculateEngagementScore($candidateList);
        $engagementSummary = $this->commitmentDataService->generateEngagementSummary($candidateList);

        return $this->render('public/candidate_list_stats.html.twig', [
            'candidateList' => $candidateList,
            'listStats' => $listStats,
            'engagementScore' => $engagementScore,
            'engagementSummary' => $engagementSummary,
            'breadcrumbItems' => [
                [
                    'label' => 'Communes',
                    'url' => $this->generateUrl('app_cities_index'),
                    'icon' => 'fas fa-city'
                ],
                [
                    'label' => $candidateList->getCity()->getName(),
                    'url' => $this->generateUrl('app_city_show', ['slug' => $candidateList->getCity()->getSlug()]),
                    'icon' => 'fas fa-map-marker-alt'
                ],
                [
                    'label' => $candidateList->getNameList(),
                    'url' => $this->generateUrl('app_candidate_list_show', ['id' => $candidateList->getId(), 'slug' => $candidateList->getSlug()]),
                    'icon' => 'fas fa-users'
                ],
                [
                    'label' => 'Statistiques',
                    'icon' => 'fas fa-chart-bar'
                ]
            ]
        ]);
    }
}
