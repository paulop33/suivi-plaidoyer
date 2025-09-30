<?php

namespace App\Repository;

use App\Entity\SpecificExpectation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SpecificExpectation>
 */
class SpecificExpectationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpecificExpectation::class);
    }

    /**
     * Trouve toutes les attentes spécifiques pour une proposition donnée
     */
    public function findByProposition(int $propositionId): array
    {
        return $this->createQueryBuilder('se')
            ->andWhere('se.proposition = :propositionId')
            ->setParameter('propositionId', $propositionId)
            ->leftJoin('se.specificity', 's')
            ->addSelect('s')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les attentes spécifiques pour une spécificité donnée
     */
    public function findBySpecificity(int $specificityId): array
    {
        return $this->createQueryBuilder('se')
            ->andWhere('se.specificity = :specificityId')
            ->setParameter('specificityId', $specificityId)
            ->leftJoin('se.proposition', 'p')
            ->addSelect('p')
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

