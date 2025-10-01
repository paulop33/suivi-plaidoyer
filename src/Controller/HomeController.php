<?php

namespace App\Controller;

use App\Service\StatisticsService;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private StatisticsService $statisticsService,
        private CategoryRepository $categoryRepository
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Récupérer toutes les catégories avec leurs données optimisées
        $categories = $this->categoryRepository->findAllWithPropositionsAndCommitments();

        // Calculer les statistiques d'engagement pour chaque catégorie
        $categoryStats = $this->statisticsService->getCategoryStatistics();

        // Récupérer les statistiques générales
        $globalStats = $this->statisticsService->getGlobalStatistics();

        return $this->render('public/index.html.twig', [
            'categories' => $categories,
            'categoryStats' => $categoryStats,
            'globalStats' => $globalStats,
            'breadcrumbItems' => [],
            'quickActions' => [
                [
                    'url' => $this->generateUrl('app_cities_index'),
                    'label' => 'Voir toutes les communes',
                    'icon' => 'fas fa-city',
                    'class' => 'btn-primary'
                ],
            ]
        ]);
    }
}
