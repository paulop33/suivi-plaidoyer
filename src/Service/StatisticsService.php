<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\CandidateList;
use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use App\Repository\CandidateListRepository;
use App\Repository\PropositionRepository;
use App\Repository\CommitmentRepository;

class StatisticsService
{
    public function __construct(
        private CategoryRepository      $categoryRepository,
        private CityRepository          $cityRepository,
        private CandidateListRepository $candidateListRepository,
        private PropositionRepository   $propositionRepository,
        private CommitmentRepository    $commitmentRepository
    ) {
    }

    /**
     * Calcule les statistiques globales du système
     */
    public function getGlobalStatistics(): array
    {
        return [
            'totalCities' => $this->cityRepository->count([]),
            'totalLists' => $this->candidateListRepository->count([]),
            'totalPropositions' => $this->propositionRepository->count([]),
            'totalCommitments' => $this->commitmentRepository->count([])
        ];
    }

    /**
     * Calcule les statistiques pour toutes les catégories
     */
    public function getCategoryStatistics(): array
    {
        $categories = $this->categoryRepository->findAll();
        $categoryStats = [];

        foreach ($categories as $category) {
            $categoryStats[$category->getId()] = $this->calculateCategoryStats($category);
        }

        return $categoryStats;
    }

    /**
     * Calcule les statistiques pour une catégorie spécifique
     */
    public function calculateCategoryStats(Category $category): array
    {
        $totalPropositions = $category->getPropositions()->count();
        $totalCommitments = 0;
        $uniqueLists = [];

        foreach ($category->getPropositions() as $proposition) {
            $commitments = $proposition->getCommitments();
            $totalCommitments += $commitments->count();

            foreach ($commitments as $commitment) {
                $listId = $commitment->getCandidateList()->getId();
                $uniqueLists[$listId] = true;
            }
        }

        return [
            'totalPropositions' => $totalPropositions,
            'totalCommitments' => $totalCommitments,
            'uniqueLists' => count($uniqueLists),
            'engagementRate' => $totalPropositions > 0 ? ($totalCommitments / $totalPropositions * 100) : 0
        ];
    }

    /**
     * Calcule les statistiques pour toutes les communes
     */
    public function getCityStatistics(): array
    {
        $cities = $this->cityRepository->findAll();
        $cityData = [];

        foreach ($cities as $city) {
            $cityData[] = $this->calculateCityStats($city);
        }

        return $cityData;
    }

    /**
     * Calcule les statistiques pour une commune spécifique
     */
    public function calculateCityStats(City $city): array
    {
        $lists = $city->getContacts();
        $totalCommitments = 0;

        foreach ($lists as $list) {
            $totalCommitments += $list->getCommitments()->count();
        }

        return [
            'city' => $city,
            'totalLists' => $lists->count(),
            'totalCommitments' => $totalCommitments,
            'engagementRate' => $lists->count() > 0 ? ($totalCommitments / $lists->count()) : 0
        ];
    }

    /**
     * Calcule les statistiques pour une liste candidate
     */
    public function calculateCandidateListStats(CandidateList $candidateList): array
    {
        $commitments = $candidateList->getCommitments();
        $categoriesCount = [];

        foreach ($commitments as $commitment) {
            $categoryId = $commitment->getProposition()->getCategory()->getId();
            $categoriesCount[$categoryId] = ($categoriesCount[$categoryId] ?? 0) + 1;
        }

        return [
            'totalCommitments' => $commitments->count(),
            'categoriesCount' => count($categoriesCount),
            'commitmentsByCategory' => $categoriesCount
        ];
    }

    /**
     * Calcule les statistiques d'engagement par commune et catégorie
     */
    public function getOverviewStatistics(): array
    {
        $cities = $this->cityRepository->findAll();
        $categories = $this->categoryRepository->findAll();
        $overviewData = [];

        foreach ($cities as $city) {
            $cityData = [
                'city' => $city,
                'categories' => []
            ];

            foreach ($categories as $category) {
                $listsInCategory = [];
                $candidateLists = $city->getContacts();

                foreach ($candidateLists as $candidateList) {
                    if ($this->hasCommitmentInCategory($candidateList, $category)) {
                        $listsInCategory[] = $candidateList;
                    }
                }

                $cityData['categories'][$category->getId()] = [
                    'category' => $category,
                    'lists' => $listsInCategory,
                    'count' => count($listsInCategory)
                ];
            }

            $overviewData[] = $cityData;
        }

        return $overviewData;
    }

    /**
     * Vérifie si une liste candidate a des engagements dans une catégorie
     */
    private function hasCommitmentInCategory(CandidateList $candidateList, Category $category): bool
    {
        foreach ($candidateList->getCommitments() as $commitment) {
            if ($commitment->getProposition()->getCategory()->getId() === $category->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Calcule le taux d'engagement global
     */
    public function getGlobalEngagementRate(): float
    {
        $totalLists = $this->candidateListRepository->count([]);
        $totalCommitments = $this->commitmentRepository->count([]);

        return $totalLists > 0 ? ($totalCommitments / $totalLists) : 0;
    }

    /**
     * Obtient les top N catégories par nombre d'engagements
     */
    public function getTopCategoriesByEngagements(int $limit = 5): array
    {
        $categoryStats = $this->getCategoryStatistics();
        $categories = $this->categoryRepository->findAll();

        $categoriesWithStats = [];
        foreach ($categories as $category) {
            $stats = $categoryStats[$category->getId()] ?? [];
            $categoriesWithStats[] = [
                'category' => $category,
                'stats' => $stats
            ];
        }

        // Trier par nombre d'engagements
        usort($categoriesWithStats, function ($a, $b) {
            return ($b['stats']['totalCommitments'] ?? 0) <=> ($a['stats']['totalCommitments'] ?? 0);
        });

        return array_slice($categoriesWithStats, 0, $limit);
    }

    /**
     * Obtient les top N communes par nombre d'engagements
     */
    public function getTopCitiesByEngagements(int $limit = 5): array
    {
        $cityStats = $this->getCityStatistics();

        // Trier par nombre d'engagements
        usort($cityStats, function ($a, $b) {
            return $b['totalCommitments'] <=> $a['totalCommitments'];
        });

        return array_slice($cityStats, 0, $limit);
    }

    /**
     * Obtient les listes les plus actives
     */
    public function getTopCandidateListsByEngagements(int $limit = 10): array
    {
        $candidateLists = $this->candidateListRepository->findAll();
        $listsWithStats = [];

        foreach ($candidateLists as $list) {
            $stats = $this->calculateCandidateListStats($list);
            if ($stats['totalCommitments'] > 0) {
                $listsWithStats[] = [
                    'candidateList' => $list,
                    'stats' => $stats
                ];
            }
        }

        // Trier par nombre d'engagements
        usort($listsWithStats, function ($a, $b) {
            return $b['stats']['totalCommitments'] <=> $a['stats']['totalCommitments'];
        });

        return array_slice($listsWithStats, 0, $limit);
    }

    /**
     * Calcule les tendances d'engagement (simulation pour l'exemple)
     */
    public function getEngagementTrends(): array
    {
        // Pour l'instant, on simule des tendances
        // Dans une vraie application, on analyserait les dates de création des engagements
        return [
            'weekly' => [
                'current' => $this->commitmentRepository->count([]),
                'previous' => $this->commitmentRepository->count([]) * 0.9,
                'trend' => 'up'
            ],
            'monthly' => [
                'current' => $this->commitmentRepository->count([]),
                'previous' => $this->commitmentRepository->count([]) * 0.85,
                'trend' => 'up'
            ]
        ];
    }
}
