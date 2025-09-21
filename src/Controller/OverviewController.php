<?php

namespace App\Controller;

use App\Service\StatisticsService;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OverviewController extends AbstractController
{
    public function __construct(
        private StatisticsService $statisticsService,
        private CategoryRepository $categoryRepository
    ) {
    }

    #[Route('/vue-ensemble', name: 'app_overview')]
    public function overview(): Response
    {
        // Utiliser le service de statistiques pour obtenir les données d'aperçu
        $overviewData = $this->statisticsService->getOverviewStatistics();
        $categories = $this->categoryRepository->findAll();

        return $this->render('public/overview.html.twig', [
            'overviewData' => $overviewData,
            'categories' => $categories,
            'breadcrumbItems' => [
                [
                    'label' => 'Vue d\'ensemble',
                    'icon' => 'fas fa-chart-bar'
                ]
            ],
            'quickActions' => [
                [
                    'url' => $this->generateUrl('app_home'),
                    'label' => 'Retour à l\'accueil',
                    'icon' => 'fas fa-home',
                    'class' => 'btn-outline-secondary'
                ],
                [
                    'url' => $this->generateUrl('app_dashboard'),
                    'label' => 'Tableau de bord',
                    'icon' => 'fas fa-chart-line',
                    'class' => 'btn-primary'
                ]
            ]
        ]);
    }

    #[Route('/statistiques', name: 'app_global_stats')]
    public function globalStats(): Response
    {
        $globalStats = $this->statisticsService->getGlobalStatistics();
        $categoryStats = $this->statisticsService->getCategoryStatistics();
        $topCategories = $this->statisticsService->getTopCategoriesByEngagements();
        $topCities = $this->statisticsService->getTopCitiesByEngagements();
        $topLists = $this->statisticsService->getTopCandidateListsByEngagements();
        $engagementRate = $this->statisticsService->getGlobalEngagementRate();
        $trends = $this->statisticsService->getEngagementTrends();

        return $this->render('public/global_stats.html.twig', [
            'globalStats' => $globalStats,
            'categoryStats' => $categoryStats,
            'topCategories' => $topCategories,
            'topCities' => $topCities,
            'topLists' => $topLists,
            'engagementRate' => $engagementRate,
            'trends' => $trends,
            'breadcrumbItems' => [
                [
                    'label' => 'Statistiques globales',
                    'icon' => 'fas fa-chart-pie'
                ]
            ]
        ]);
    }
}
