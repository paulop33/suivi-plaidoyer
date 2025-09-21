<?php

namespace App\Controller;

use App\Entity\City;
use App\Service\CommitmentDataService;
use App\Service\StatisticsService;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/communes')]
class CityController extends AbstractController
{
    public function __construct(
        private CityRepository $cityRepository,
        private CommitmentDataService $commitmentDataService,
        private StatisticsService $statisticsService
    ) {
    }

    #[Route('', name: 'app_cities_index')]
    public function index(): Response
    {
        // Utiliser la méthode optimisée du repository
        $cityData = $this->statisticsService->getCityStatistics();

        return $this->render('public/cities_index.html.twig', [
            'cityData' => $cityData,
            'breadcrumbItems' => [
                [
                    'label' => 'Communes',
                    'icon' => 'fas fa-city'
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

    #[Route('/{slug}', name: 'app_city_show')]
    public function show(string $slug): Response
    {
        // Utiliser la méthode optimisée du repository
        $city = $this->cityRepository->findOneBySlugWithAllData($slug);

        if (!$city) {
            throw $this->createNotFoundException('Commune non trouvée');
        }

        // Organiser les données des listes de la commune avec leurs engagements
        $cityListData = $this->commitmentDataService->organizeCityListData($city);

        // Organiser les propositions avec les listes qui les ont signées
        $propositionData = $this->commitmentDataService->organizeCityPropositionData($city);

        // Calculer les statistiques de la commune
        $cityStats = $this->statisticsService->calculateCityStats($city);

        // Obtenir la répartition des engagements par catégorie
        $engagementDistribution = $this->commitmentDataService->getCityEngagementDistribution($city);

        return $this->render('public/city_show.html.twig', [
            'city' => $city,
            'cityListData' => $cityListData,
            'propositionData' => $propositionData,
            'cityStats' => $cityStats,
            'engagementDistribution' => $engagementDistribution,
            'breadcrumbItems' => [
                [
                    'label' => 'Communes',
                    'url' => $this->generateUrl('app_cities_index'),
                    'icon' => 'fas fa-city'
                ],
                [
                    'label' => $city->getName(),
                    'icon' => 'fas fa-map-marker-alt'
                ]
            ],
            'quickActions' => [
                [
                    'url' => $this->generateUrl('app_cities_index'),
                    'label' => 'Toutes les communes',
                    'icon' => 'fas fa-city',
                    'class' => 'btn-outline-primary'
                ]
            ]
        ]);
    }
}
