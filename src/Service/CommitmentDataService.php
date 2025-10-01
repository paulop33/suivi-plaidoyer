<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\CandidateList;
use App\Entity\Proposition;
use App\Enum\CommitmentStatus;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommitmentDataService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CityRepository $cityRepository
    ) {
    }
    /**
     * Organise les engagements d'une proposition par commune
     */
    public function organizeCommitmentsByCity(Proposition $proposition): array
    {
        $commitments = $proposition->getCommitments();
        $commitmentsByCity = [];

        foreach ($commitments as $commitment) {
            $candidateList = $commitment->getCandidateList();
            $city = $candidateList->getCity();
            $cityName = $city->getName();

            if (!isset($commitmentsByCity[$cityName])) {
                $commitmentsByCity[$cityName] = [
                    'city' => $city,
                    'lists' => []
                ];
            }

            $commitmentsByCity[$cityName]['lists'][] = [
                'candidateList' => $candidateList,
                'commitment' => $commitment
            ];
        }

        return $commitmentsByCity;
    }

    /**
     * Organise toutes les communes avec leurs engagements pour une proposition
     * Affiche toutes les communes, même celles sans engagement
     */
    public function organizeAllCitiesForProposition(Proposition $proposition): array
    {
        // Récupérer toutes les communes
        $allCities = $this->cityRepository->findBy([], ['name' => 'ASC']);

        // Organiser les engagements existants par commune
        $commitments = $proposition->getCommitments();
        $commitmentsByCity = [];

        foreach ($commitments as $commitment) {
            $candidateList = $commitment->getCandidateList();
            $city = $candidateList->getCity();
            $cityName = $city->getName();

            if (!isset($commitmentsByCity[$cityName])) {
                $commitmentsByCity[$cityName] = [];
            }

            $commitmentsByCity[$cityName][] = [
                'candidateList' => $candidateList,
                'commitment' => $commitment
            ];
        }

        // Créer le tableau final avec toutes les communes
        $result = [];
        foreach ($allCities as $city) {
            $cityName = $city->getName();
            $result[$cityName] = [
                'city' => $city,
                'lists' => $commitmentsByCity[$cityName] ?? []
            ];
        }

        return $result;
    }

    /**
     * Organise les engagements d'une liste candidate par catégorie
     */
    public function organizeCommitmentsByCategory(CandidateList $candidateList): array
    {
        $commitments = $candidateList->getCommitments();
        $commitmentsByCategory = [];

        foreach ($commitments as $commitment) {
            $proposition = $commitment->getProposition();
            $category = $proposition->getCategory();
            $categoryName = $category->getName();

            if (!isset($commitmentsByCategory[$categoryName])) {
                $commitmentsByCategory[$categoryName] = [
                    'category' => $category,
                    'propositions' => []
                ];
            }

            $commitmentsByCategory[$categoryName]['propositions'][] = [
                'proposition' => $proposition,
                'commitment' => $commitment
            ];
        }

        // Sort categories by position
        uasort($commitmentsByCategory, function ($a, $b) {
            $positionA = $a['category']->getPosition() ?? PHP_INT_MAX;
            $positionB = $b['category']->getPosition() ?? PHP_INT_MAX;

            if ($positionA === $positionB) {
                return $a['category']->getId() <=> $b['category']->getId();
            }

            return $positionA <=> $positionB;
        });

        // Sort propositions within each category by position
        foreach ($commitmentsByCategory as &$categoryData) {
            usort($categoryData['propositions'], function ($a, $b) {
                $positionA = $a['proposition']->getPosition() ?? PHP_INT_MAX;
                $positionB = $b['proposition']->getPosition() ?? PHP_INT_MAX;

                if ($positionA === $positionB) {
                    return $a['proposition']->getId() <=> $b['proposition']->getId();
                }

                return $positionA <=> $positionB;
            });
        }

        return $commitmentsByCategory;
    }

    /**
     * Organise les données des propositions d'une catégorie avec leurs signatures
     */
    public function organizePropositionData(Category $category): array
    {
        $propositions = $category->getPropositions();
        $propositionData = [];

        foreach ($propositions as $proposition) {
            $commitments = $proposition->getCommitments();
            $signedLists = [];
            $citiesWithSignatures = [];

            foreach ($commitments as $commitment) {
                $candidateList = $commitment->getCandidateList();
                $city = $candidateList->getCity();

                $signedLists[] = [
                    'candidateList' => $candidateList,
                    'city' => $city,
                    'commitment' => $commitment
                ];

                $cityName = $city->getName();
                if (!isset($citiesWithSignatures[$cityName])) {
                    $citiesWithSignatures[$cityName] = [];
                }
                $citiesWithSignatures[$cityName][] = $candidateList;
            }

            $propositionData[] = [
                'proposition' => $proposition,
                'signedLists' => $signedLists,
                'citiesWithSignatures' => $citiesWithSignatures,
                'totalSignatures' => count($signedLists)
            ];
        }

        return $propositionData;
    }

    /**
     * Organise les données des listes d'une commune avec leurs engagements
     */
    public function organizeCityListData(City $city): array
    {
        $candidateLists = $city->getContacts();
        $listData = [];

        foreach ($candidateLists as $candidateList) {
            $commitmentsByCategory = $this->organizeCommitmentsByCategory($candidateList);

            $listData[] = [
                'candidateList' => $candidateList,
                'commitmentsByCategory' => $commitmentsByCategory,
                'positivesCommitmentsByCategory' => $this->filterAcceptedCommitmentsByCategory($commitmentsByCategory),
                'totalPositivesCommitments' => $candidateList->getPositiveCommitments()->count(),
            ];
        }

        return $listData;
    }

    /**
     * Compte uniquement les engagements acceptés dans une liste d'engagements
     */
    public function countAcceptedCommitments(array $commitmentData): int
    {
        $acceptedCount = 0;
        foreach ($commitmentData as $data) {
            $commitment = $data['commitment'];
            if ($commitment->isAccepted()) {
                $acceptedCount++;
            }
        }
        return $acceptedCount;
    }

    /**
     * Filtre les engagements pour ne garder que ceux acceptés
     */
    public function filterAcceptedCommitments(array $commitmentData): array
    {
        return array_filter($commitmentData, function ($data) {
            $commitment = $data['commitment'];
            return $commitment->isAccepted();
        });
    }

    /**
     * Organise toutes les propositions avec les listes d'une commune qui les ont signées
     */
    public function organizeCityPropositionData(City $city): array
    {
        // Récupérer toutes les propositions existantes
        $propositionRepository = $this->entityManager->getRepository(\App\Entity\Proposition::class);
        $allPropositions = $propositionRepository->findAllWithCommitments();

        $candidateLists = $city->getContacts();
        $candidateListIds = array_map(fn($list) => $list->getId(), $candidateLists->toArray());

        // Organiser par catégorie
        $propositionsByCategory = [];

        foreach ($allPropositions as $proposition) {
            $category = $proposition->getCategory();
            $categoryName = $category->getName();

            if (!isset($propositionsByCategory[$categoryName])) {
                $propositionsByCategory[$categoryName] = [
                    'category' => $category,
                    'propositions' => []
                ];
            }

            // Trouver les listes de cette commune qui ont signé cette proposition
            $signedListsFromCity = [];
            foreach ($proposition->getCommitments() as $commitment) {
                $candidateList = $commitment->getCandidateList();
                if (in_array($candidateList->getId(), $candidateListIds)) {
                    $signedListsFromCity[] = [
                        'candidateList' => $candidateList,
                        'commitment' => $commitment
                    ];
                }
            }

            // Compter uniquement les engagements acceptés
            $acceptedSignatures = $this->countAcceptedCommitments($signedListsFromCity);

            $propositionsByCategory[$categoryName]['propositions'][] = [
                'proposition' => $proposition,
                'signedLists' => $signedListsFromCity,
                'totalSignatures' => $acceptedSignatures,
                'totalSignaturesGlobal' => $proposition->getCommitments()->count(),
                'totalAllCommitments' => count($signedListsFromCity) // Garde l'ancien comptage pour référence
            ];
        }

        return $propositionsByCategory;
    }



    /**
     * Filtre les engagements par période
     */
    public function filterCommitmentsByPeriod(array $commitments, \DateTime $startDate, \DateTime $endDate): array
    {
        return array_filter($commitments, function ($commitment) use ($startDate, $endDate) {
            $creationDate = $commitment->getCreationDate();
            return $creationDate >= $startDate && $creationDate <= $endDate;
        });
    }

    /**
     * Groupe les engagements par mois
     */
    public function groupCommitmentsByMonth(array $commitments): array
    {
        $groupedCommitments = [];

        foreach ($commitments as $commitment) {
            $monthKey = $commitment->getCreationDate()->format('Y-m');
            if (!isset($groupedCommitments[$monthKey])) {
                $groupedCommitments[$monthKey] = [];
            }
            $groupedCommitments[$monthKey][] = $commitment;
        }

        return $groupedCommitments;
    }

    /**
     * Calcule la répartition des engagements par catégorie pour une commune
     */
    public function getCityEngagementDistribution(City $city): array
    {
        $distribution = [];
        $candidateLists = $city->getContacts();

        foreach ($candidateLists as $candidateList) {
            foreach ($candidateList->getCommitments() as $commitment) {
                $categoryName = $commitment->getProposition()->getCategory()->getName();
                $distribution[$categoryName] = ($distribution[$categoryName] ?? 0) + 1;
            }
        }

        return $distribution;
    }

    /**
     * Trouve les listes candidates qui ont signé une proposition spécifique
     */
    public function getListsForProposition(Proposition $proposition): array
    {
        $lists = [];
        foreach ($proposition->getCommitments() as $commitment) {
            $lists[] = $commitment->getCandidateList();
        }
        return $lists;
    }

    /**
     * Trouve les propositions signées par une liste candidate dans une catégorie
     */
    public function getPropositionsForListInCategory(CandidateList $candidateList, Category $category): array
    {
        $propositions = [];
        foreach ($candidateList->getCommitments() as $commitment) {
            $proposition = $commitment->getProposition();
            if ($proposition->getCategory()->getId() === $category->getId()) {
                $propositions[] = [
                    'proposition' => $proposition,
                    'commitment' => $commitment
                ];
            }
        }
        return $propositions;
    }

    /**
     * Calcule le score d'engagement d'une liste (basé sur les barèmes)
     */
    public function calculateEngagementScore(CandidateList $candidateList): int
    {
        $score = 0;
        foreach ($candidateList->getCommitments() as $commitment) {
            $proposition = $commitment->getProposition();
            $score += $proposition->getBareme() ?? 0;

            // Bonus pour la catégorie si elle a un barème
            $category = $proposition->getCategory();
            $score += $category->getBareme() ?? 0;
        }
        return $score;
    }

    /**
     * Trouve les communes les plus actives pour une catégorie
     */
    public function getActiveCitiesForCategory(Category $category): array
    {
        $cityEngagements = [];

        foreach ($category->getPropositions() as $proposition) {
            foreach ($proposition->getCommitments() as $commitment) {
                $cityName = $commitment->getCandidateList()->getCity()->getName();
                $cityEngagements[$cityName] = ($cityEngagements[$cityName] ?? 0) + 1;
            }
        }

        // Trier par nombre d'engagements
        arsort($cityEngagements);

        return $cityEngagements;
    }

    /**
     * Génère un résumé des engagements pour une entité
     */
    public function generateEngagementSummary($entity): array
    {
        $summary = [
            'totalCommitments' => 0,
            'categoriesInvolved' => [],
            'citiesInvolved' => [],
            'listsInvolved' => []
        ];

        if ($entity instanceof Category) {
            foreach ($entity->getPropositions() as $proposition) {
                foreach ($proposition->getCommitments() as $commitment) {
                    $summary['totalCommitments']++;
                    $cityName = $commitment->getCandidateList()->getCity()->getName();
                    $listName = $commitment->getCandidateList()->getNameList();

                    $summary['citiesInvolved'][$cityName] = true;
                    $summary['listsInvolved'][$listName] = true;
                }
            }
        } elseif ($entity instanceof City) {
            foreach ($entity->getContacts() as $candidateList) {
                foreach ($candidateList->getCommitments() as $commitment) {
                    $summary['totalCommitments']++;
                    $categoryName = $commitment->getProposition()->getCategory()->getName();
                    $listName = $candidateList->getNameList();

                    $summary['categoriesInvolved'][$categoryName] = true;
                    $summary['listsInvolved'][$listName] = true;
                }
            }
        } elseif ($entity instanceof CandidateList) {
            foreach ($entity->getCommitments() as $commitment) {
                $summary['totalCommitments']++;
                $categoryName = $commitment->getProposition()->getCategory()->getName();
                $cityName = $entity->getCity()->getName();

                $summary['categoriesInvolved'][$categoryName] = true;
                $summary['citiesInvolved'][$cityName] = true;
            }
        }

        // Convertir les tableaux associatifs en compteurs
        $summary['categoriesInvolved'] = count($summary['categoriesInvolved']);
        $summary['citiesInvolved'] = count($summary['citiesInvolved']);
        $summary['listsInvolved'] = count($summary['listsInvolved']);

        return $summary;
    }

    private function filterAcceptedCommitmentsByCategory(array $commitmentsByCategory): array
    {
        return array_map(function ($commitments) {
            return $this->filterAcceptedCommitments($commitments['propositions']);
        }, $commitmentsByCategory);
    }
}
