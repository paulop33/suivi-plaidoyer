<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\CandidateList;
use App\Enum\CommitmentStatus;
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
            'totalCommitments' => $this->commitmentRepository->count(['status' => CommitmentStatus::ACCEPTED->value])
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
        $totalPositivesCommitments = 0;
        $uniqueLists = [];

        foreach ($category->getPropositions() as $proposition) {
            $commitments = $proposition->getCommitments();
            $positivesCommitments = $proposition->getPositivesCommitments();
            $totalCommitments += $commitments->count();
            $totalPositivesCommitments += $positivesCommitments->count();

            foreach ($positivesCommitments as $commitment) {
                $listId = $commitment->getCandidateList()->getId();
                $uniqueLists[$listId] = true;
            }
        }

        return [
            'totalPropositions' => $totalPropositions,
            'totalCommitments' => $totalCommitments,
            'totalPositivesCommitments' => $totalPositivesCommitments,
            'uniqueLists' => count($uniqueLists),
            'engagementRate' => $totalPropositions > 0 ? ($totalPositivesCommitments / $totalPropositions * 100) : 0
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
            $totalCommitments += $list->getPositiveCommitments()->count();
        }

        return [
            'city' => $city,
            'totalLists' => $lists->count(),
            'totalCommitments' => $totalCommitments,
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
}
