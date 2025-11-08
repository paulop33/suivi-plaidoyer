<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\ElectedList;
use App\Enum\ImplementationStatus;
use App\Repository\CityRepository;
use App\Repository\ElectedListRepository;
use App\Repository\ProgressUpdateRepository;
use App\Repository\CommitmentRepository;

class ProgressTrackingService
{
    public function __construct(
        private CityRepository $cityRepository,
        private ElectedListRepository $electedListRepository,
        private ProgressUpdateRepository $progressUpdateRepository,
        private CommitmentRepository $commitmentRepository
    ) {
    }

    /**
     * Obtient une vue d'ensemble de la progression pour toutes les villes
     */
    public function getAllCitiesProgressOverview(): array
    {
        $electedLists = $this->electedListRepository->findCurrentMandate();
        $citiesProgress = [];

        foreach ($electedLists as $electedList) {
            $city = $electedList->getCity();
            $progressStats = $this->getCityProgressStats($city, $electedList);

            $citiesProgress[] = [
                'city' => $city,
                'electedList' => $electedList,
                'stats' => $progressStats,
                'hasProgress' => $progressStats['totalUpdates'] > 0
            ];
        }

        // Trier par nom de ville
        usort($citiesProgress, function($a, $b) {
            return $a['city']->getName() <=> $b['city']->getName();
        });

        return $citiesProgress;
    }

    /**
     * Calcule les statistiques de progression pour une ville
     */
    public function getCityProgressStats(City $city, ElectedList $electedList): array
    {
        // Récupérer tous les engagements acceptés de la liste candidate
        $acceptedCommitments = $this->commitmentRepository->createQueryBuilder('c')
            ->andWhere('c.candidateList = :candidateList')
            ->andWhere('c.status = :status')
            ->setParameter('candidateList', $electedList->getCandidateList())
            ->setParameter('status', \App\Enum\CommitmentStatus::ACCEPTED)
            ->getQuery()
            ->getResult();

        $totalCommitments = count($acceptedCommitments);

        if ($totalCommitments === 0) {
            return [
                'totalCommitments' => 0,
                'totalUpdates' => 0,
                'statusDistribution' => [],
                'commitmentsWithProgress' => 0,
                'progressPercentage' => 0
            ];
        }

        // Compter les engagements avec suivi
        $commitmentsWithProgress = 0;
        $totalProgress = 0;
        $statusCounts = [];

        foreach ($acceptedCommitments as $commitment) {
            $latestUpdate = $commitment->getLatestProgressUpdate();
            if ($latestUpdate) {
                $commitmentsWithProgress++;
                $totalProgress += $latestUpdate->getEffectiveProgressPercentage();

                $status = $latestUpdate->getStatus()->value;
                $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
            }
        }

        $progressPercentage = $totalCommitments > 0 ? ($commitmentsWithProgress / $totalCommitments) * 100 : 0;

        $totalUpdates = 0;
        try {
            $totalUpdates = $this->progressUpdateRepository->createQueryBuilder('pu')
                ->select('COUNT(pu.id)')
                ->andWhere('pu.electedList = :electedList')
                ->setParameter('electedList', $electedList)
                ->getQuery()
                ->getSingleScalarResult() ?? 0;
        } catch (\Exception $e) {
            $totalUpdates = 0;
        }

        return [
            'totalCommitments' => $totalCommitments,
            'totalUpdates' => (int) $totalUpdates,
            'statusDistribution' => $statusCounts,
            'commitmentsWithProgress' => $commitmentsWithProgress,
            'progressPercentage' => round($progressPercentage, 1)
        ];
    }

    /**
     * Obtient les données détaillées pour une ville
     */
    public function getCityProgressData(City $city, ElectedList $electedList): array
    {
        // Récupérer TOUS les engagements acceptés de la liste candidate
        $acceptedCommitments = $this->commitmentRepository->createQueryBuilder('c')
            ->andWhere('c.candidateList = :candidateList')
            ->andWhere('c.status = :status')
            ->setParameter('candidateList', $electedList->getCandidateList())
            ->setParameter('status', \App\Enum\CommitmentStatus::ACCEPTED)
            ->getQuery()
            ->getResult();

        // Créer un tableau avec tous les engagements et leurs dernières mises à jour
        $allCommitmentsData = [];
        foreach ($acceptedCommitments as $commitment) {
            $latestUpdate = $commitment->getLatestProgressUpdate();
            $allCommitmentsData[] = [
                'commitment' => $commitment,
                'latestUpdate' => $latestUpdate,
                'hasUpdate' => $latestUpdate !== null
            ];
        }

        // Organiser par catégorie
        $progressByCategory = [];
        foreach ($allCommitmentsData as $data) {
            $category = $data['commitment']->getProposition()->getCategory();
            $categoryName = $category->getName();

            if (!isset($progressByCategory[$categoryName])) {
                $progressByCategory[$categoryName] = [
                    'category' => $category,
                    'commitments' => [],
                    'totalCommitments' => 0,
                    'commitmentsWithProgress' => 0
                ];
            }

            $progressByCategory[$categoryName]['commitments'][] = $data;
            $progressByCategory[$categoryName]['totalCommitments']++;

            if ($data['hasUpdate']) {
                $progressByCategory[$categoryName]['commitmentsWithProgress']++;
            }
        }

        return [
            'allCommitmentsData' => $allCommitmentsData,
            'progressByCategory' => $progressByCategory
        ];
    }

    /**
     * Obtient la progression par catégorie pour une liste élue
     */
    public function getProgressByCategory(ElectedList $electedList): array
    {
        $updates = $this->progressUpdateRepository->findLatestByCommitmentForElectedList($electedList);
        $categoryProgress = [];

        foreach ($updates as $update) {
            $category = $update->getCommitment()->getProposition()->getCategory();
            $categoryId = $category->getId();

            if (!isset($categoryProgress[$categoryId])) {
                $categoryProgress[$categoryId] = [
                    'category' => $category,
                    'totalCommitments' => 0,
                    'totalProgress' => 0,
                    'averageProgress' => 0,
                    'statusCounts' => []
                ];
            }

            $categoryProgress[$categoryId]['totalCommitments']++;
            $categoryProgress[$categoryId]['totalProgress'] += $update->getEffectiveProgressPercentage();

            $status = $update->getStatus()->value;
            $categoryProgress[$categoryId]['statusCounts'][$status] =
                ($categoryProgress[$categoryId]['statusCounts'][$status] ?? 0) + 1;
        }

        // Calculer les moyennes
        foreach ($categoryProgress as &$data) {
            $data['averageProgress'] = $data['totalCommitments'] > 0
                ? round($data['totalProgress'] / $data['totalCommitments'], 1)
                : 0;
        }

        return $categoryProgress;
    }

    /**
     * Obtient les statistiques globales de progression
     */
    public function getGlobalProgressStats(): array
    {
        $electedLists = $this->electedListRepository->findCurrentMandate();
        $totalCities = count($electedLists);
        $citiesWithProgress = 0;

        foreach ($electedLists as $electedList) {
            $stats = $this->getCityProgressStats($electedList->getCity(), $electedList);
            if ($stats['totalUpdates'] > 0) {
                $citiesWithProgress++;
            }
        }

        $globalStats = $this->progressUpdateRepository->getGlobalProgressStats();

        return [
            'totalCities' => $totalCities,
            'citiesWithProgress' => $citiesWithProgress,
            'totalUpdates' => $globalStats['totalUpdates'],
            'statusDistribution' => $globalStats['statusDistribution']
        ];
    }

    /**
     * Obtient des statistiques détaillées globales
     */
    public function getDetailedGlobalStats(): array
    {
        $basicStats = $this->getGlobalProgressStats();

        // Calculer le nombre total d'engagements suivis
        $totalCommitments = 0;
        $electedLists = $this->electedListRepository->findCurrentMandate();
        foreach ($electedLists as $electedList) {
            $stats = $this->getCityProgressStats($electedList->getCity(), $electedList);
            $totalCommitments += $stats['totalCommitments'];
        }

        // Ajouter des métriques supplémentaires
        $staleCommitments = $this->progressUpdateRepository->findStaleCommitments(90);
        $recentUpdates = $this->progressUpdateRepository->findRecentUpdates(50);

        return array_merge($basicStats, [
            'totalCommitments' => $totalCommitments,
            'staleCommitments' => count($staleCommitments),
            'recentUpdatesCount' => count($recentUpdates),
            'recentUpdates' => array_slice($recentUpdates, 0, 10) // Limiter à 10 pour l'affichage
        ]);
    }

    /**
     * Obtient les tendances de progression (données pour graphiques)
     */
    public function getProgressTrends(): array
    {
        // Implémentation simplifiée - peut être étendue avec des données temporelles
        $statusDistribution = $this->progressUpdateRepository->getGlobalProgressStats()['statusDistribution'];

        $trends = [];
        foreach ($statusDistribution as $statusData) {
            $status = $statusData['status'];
            // Si c'est une string, convertir en enum
            if (is_string($status)) {
                $status = ImplementationStatus::fromValue($status);
            }
            // Si c'est déjà un enum ou si la conversion a réussi
            if ($status instanceof ImplementationStatus) {
                $trends[] = [
                    'label' => $status->getLabel(),
                    'value' => $statusData['count'],
                    'color' => $status->getColor()
                ];
            }
        }

        return $trends;
    }

    /**
     * Obtient les villes les plus performantes
     */
    public function getTopPerformingCities(int $limit = 10): array
    {
        $citiesProgress = $this->getAllCitiesProgressOverview();

        // Filtrer les villes avec du progrès et limiter
        $topCities = array_filter($citiesProgress, function($cityData) {
            return $cityData['hasProgress'];
        });

        return array_slice($topCities, 0, $limit);
    }
}
