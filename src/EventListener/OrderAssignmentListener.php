<?php

namespace App\EventListener;

use App\Entity\Category;
use App\Entity\Proposition;
use App\Service\OrderManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Listener pour assigner automatiquement l'ordre aux entités
 */
#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Category::class)]
#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Proposition::class)]
class OrderAssignmentListener
{
    public function __construct(
        private OrderManager $orderManager
    ) {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Category) {
            $this->orderManager->assignCategoryOrder($entity);
        } elseif ($entity instanceof Proposition) {
            $this->orderManager->assignPropositionOrder($entity);
        }
    }
}
