<?php

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use App\Repository\ElectedListRepository;
use App\Repository\ProgressUpdateRepository;
use App\Service\ProgressTrackingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/suivi-mandature')]
class ProgressTrackingController extends AbstractController
{
    public function __construct(
        private CityRepository $cityRepository,
        private ElectedListRepository $electedListRepository,
        private ProgressUpdateRepository $progressUpdateRepository,
        private ProgressTrackingService $progressTrackingService
    ) {
    }

    #[Route('', name: 'app_progress_tracking_index')]
    public function index(): Response
    {
        // Vue d'ensemble de toutes les villes avec leurs statistiques de progression
        $citiesWithProgress = $this->progressTrackingService->getAllCitiesProgressOverview();
        $globalStats = $this->progressTrackingService->getGlobalProgressStats();

        return $this->render('progress_tracking/index.html.twig', [
            'citiesWithProgress' => $citiesWithProgress,
            'globalStats' => $globalStats,
            'breadcrumbItems' => [
                [
                    'label' => 'Suivi des mandatures',
                    'icon' => 'fas fa-chart-line'
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

    #[Route('/commune/{slug}', name: 'app_progress_tracking_city')]
    public function cityProgress(string $slug): Response
    {
        $city = $this->cityRepository->findOneBy(['slug' => $slug]);

        if (!$city) {
            throw $this->createNotFoundException('Commune non trouvée');
        }

        $electedList = $this->electedListRepository->findByCity($city);

        if (!$electedList) {
            // Ville sans liste élue - afficher un message informatif
            return $this->render('progress_tracking/city_no_elected_list.html.twig', [
                'city' => $city,
                'breadcrumbItems' => [
                    [
                        'label' => 'Suivi des mandatures',
                        'url' => $this->generateUrl('app_progress_tracking_index'),
                        'icon' => 'fas fa-chart-line'
                    ],
                    [
                        'label' => $city->getName(),
                        'icon' => 'fas fa-map-marker-alt'
                    ]
                ]
            ]);
        }

        // Récupérer les données de progression pour cette ville
        $progressData = $this->progressTrackingService->getCityProgressData($city, $electedList);

        return $this->render('progress_tracking/city_progress.html.twig', [
            'city' => $city,
            'electedList' => $electedList,
            'allCommitmentsData' => $progressData['allCommitmentsData'],
            'progressByCategory' => $progressData['progressByCategory'],
            'breadcrumbItems' => [
                [
                    'label' => 'Suivi des mandatures',
                    'url' => $this->generateUrl('app_progress_tracking_index'),
                    'icon' => 'fas fa-chart-line'
                ],
                [
                    'label' => $city->getName(),
                    'icon' => 'fas fa-map-marker-alt'
                ]
            ],
            'quickActions' => [
                [
                    'url' => $this->generateUrl('app_progress_tracking_index'),
                    'label' => 'Toutes les communes',
                    'icon' => 'fas fa-list',
                    'class' => 'btn-outline-primary'
                ],
                [
                    'url' => $this->generateUrl('app_city_show', ['slug' => $city->getSlug()]),
                    'label' => 'Voir les engagements',
                    'icon' => 'fas fa-check',
                    'class' => 'btn-outline-secondary'
                ]
            ]
        ]);
    }

    #[Route('/engagement/{id}/historique', name: 'app_progress_tracking_commitment_history')]
    public function commitmentHistory(int $id): Response
    {
        $progressUpdates = $this->progressUpdateRepository->createQueryBuilder('pu')
            ->leftJoin('pu.commitment', 'c')
            ->leftJoin('c.proposition', 'p')
            ->leftJoin('pu.electedList', 'el')
            ->leftJoin('el.city', 'city')
            ->leftJoin('pu.updatedBy', 'u')
            ->addSelect('c', 'p', 'el', 'city', 'u')
            ->andWhere('c.id = :commitmentId')
            ->setParameter('commitmentId', $id)
            ->orderBy('pu.updateDate', 'DESC')
            ->getQuery()
            ->getResult();

        if (empty($progressUpdates)) {
            throw $this->createNotFoundException('Aucun suivi trouvé pour cet engagement');
        }

        $commitment = $progressUpdates[0]->getCommitment();
        $city = $progressUpdates[0]->getElectedList()->getCity();

        return $this->render('progress_tracking/commitment_history.html.twig', [
            'commitment' => $commitment,
            'progressUpdates' => $progressUpdates,
            'city' => $city,
            'breadcrumbItems' => [
                [
                    'label' => 'Suivi des mandatures',
                    'url' => $this->generateUrl('app_progress_tracking_index'),
                    'icon' => 'fas fa-chart-line'
                ],
                [
                    'label' => $city->getName(),
                    'url' => $this->generateUrl('app_progress_tracking_city', ['slug' => $city->getSlug()]),
                    'icon' => 'fas fa-map-marker-alt'
                ],
                [
                    'label' => 'Historique engagement',
                    'icon' => 'fas fa-history'
                ]
            ]
        ]);
    }

    #[Route('/statistiques', name: 'app_progress_tracking_stats')]
    public function statistics(): Response
    {
        $globalStats = $this->progressTrackingService->getDetailedGlobalStats();
        $progressTrends = $this->progressTrackingService->getProgressTrends();
        $topPerformingCities = $this->progressTrackingService->getTopPerformingCities();

        return $this->render('progress_tracking/statistics.html.twig', [
            'globalStats' => $globalStats,
            'progressTrends' => $progressTrends,
            'topPerformingCities' => $topPerformingCities,
            'breadcrumbItems' => [
                [
                    'label' => 'Suivi des mandatures',
                    'url' => $this->generateUrl('app_progress_tracking_index'),
                    'icon' => 'fas fa-chart-line'
                ],
                [
                    'label' => 'Statistiques',
                    'icon' => 'fas fa-chart-bar'
                ]
            ]
        ]);
    }
}
