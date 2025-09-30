<?php

namespace App\Repository;

use App\Entity\Specificity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Specificity>
 */
class SpecificityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Specificity::class);
    }

    /**
     * Trouve toutes les spécificités avec leurs villes
     */
    public function findAllWithCities(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.cities', 'c')
            ->addSelect('c')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une spécificité par son slug
     */
    public function findOneBySlug(string $slug): ?Specificity
    {
        return $this->createQueryBuilder('s')
            ->where('s.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve toutes les spécificités avec leurs propositions
     */
    public function findAllWithPropositions(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.propositions', 'p')
            ->addSelect('p')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

