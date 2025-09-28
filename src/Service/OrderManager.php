<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Proposition;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer l'ordre des entités
 */
class OrderManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Assigne automatiquement un ordre à une catégorie si elle n'en a pas
     */
    public function assignCategoryOrder(Category $category): void
    {
        if ($category->getPosition() !== null) {
            return;
        }

        $maxOrder = $this->getMaxCategoryOrder();
        $category->setPosition($maxOrder + 1);
    }

    /**
     * Assigne automatiquement un ordre à une proposition si elle n'en a pas
     */
    public function assignPropositionOrder(Proposition $proposition): void
    {
        if ($proposition->getPosition() !== null) {
            return;
        }

        $maxOrder = $this->getMaxPropositionOrderInCategory($proposition->getCategory());
        $proposition->setPosition($maxOrder + 1);
    }

    /**
     * Obtient l'ordre maximum des catégories
     */
    public function getMaxCategoryOrder(): int
    {
        $result = $this->entityManager
            ->createQuery('SELECT MAX(c.position) FROM App\Entity\Category c WHERE c.position IS NOT NULL')
            ->getSingleScalarResult();

        return $result ?? 0;
    }

    /**
     * Obtient l'ordre maximum des propositions dans une catégorie
     */
    public function getMaxPropositionOrderInCategory(?Category $category): int
    {
        if (!$category || !$category->getId()) {
            return 0;
        }

        $result = $this->entityManager
            ->createQuery('SELECT MAX(p.position) FROM App\Entity\Proposition p WHERE p.category = :category AND p.position IS NOT NULL')
            ->setParameter('category', $category)
            ->getSingleScalarResult();

        return $result ?? 0;
    }

    /**
     * Réorganise l'ordre des catégories pour éliminer les trous
     */
    public function reorderCategories(): void
    {
        $categories = $this->entityManager
            ->getRepository(Category::class)
            ->findBy([], ['position' => 'ASC', 'id' => 'ASC']);

        $order = 1;
        foreach ($categories as $category) {
            $category->setPosition($order++);
        }

        $this->entityManager->flush();
    }

    /**
     * Réorganise l'ordre des propositions dans une catégorie pour éliminer les trous
     */
    public function reorderPropositionsInCategory(Category $category): void
    {
        $propositions = $this->entityManager
            ->getRepository(Proposition::class)
            ->findBy(['category' => $category], ['position' => 'ASC', 'id' => 'ASC']);

        $order = 1;
        foreach ($propositions as $proposition) {
            $proposition->setPosition($order++);
        }

        $this->entityManager->flush();
    }

    /**
     * Déplace une catégorie vers le haut dans l'ordre
     */
    public function moveCategoryUp(Category $category): bool
    {
        $currentOrder = $category->getPosition();
        if ($currentOrder === null || $currentOrder <= 1) {
            return false;
        }

        $previousCategory = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy(['ordre' => $currentOrder - 1]);

        if ($previousCategory) {
            $category->setPosition($currentOrder - 1);
            $previousCategory->setPosition($currentOrder);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }

    /**
     * Déplace une catégorie vers le bas dans l'ordre
     */
    public function moveCategoryDown(Category $category): bool
    {
        $currentOrder = $category->getPosition();
        if ($currentOrder === null) {
            return false;
        }

        $nextCategory = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy(['position' => $currentOrder + 1]);

        if ($nextCategory) {
            $category->setPosition($currentOrder + 1);
            $nextCategory->setPosition($currentOrder);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }

    /**
     * Déplace une proposition vers le haut dans l'ordre
     */
    public function movePropositionUp(Proposition $proposition): bool
    {
        $currentOrder = $proposition->getPosition();
        if ($currentOrder === null || $currentOrder <= 1) {
            return false;
        }

        $previousProposition = $this->entityManager
            ->getRepository(Proposition::class)
            ->findOneBy([
                'category' => $proposition->getCategory(),
                'position' => $currentOrder - 1
            ]);

        if ($previousProposition) {
            $proposition->setPosition($currentOrder - 1);
            $previousProposition->setPosition($currentOrder);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }

    /**
     * Déplace une proposition vers le bas dans l'ordre
     */
    public function movePropositionDown(Proposition $proposition): bool
    {
        $currentOrder = $proposition->getPosition();
        if ($currentOrder === null) {
            return false;
        }

        $nextProposition = $this->entityManager
            ->getRepository(Proposition::class)
            ->findOneBy([
                'category' => $proposition->getCategory(),
                'position' => $currentOrder + 1
            ]);

        if ($nextProposition) {
            $proposition->setPosition($currentOrder + 1);
            $nextProposition->setPosition($currentOrder);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }
}
