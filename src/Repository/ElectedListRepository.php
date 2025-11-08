<?php

namespace App\Repository;

use App\Entity\ElectedList;
use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ElectedList>
 */
class ElectedListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectedList::class);
    }

    /**
     * Trouve la liste élue pour une ville donnée
     */
    public function findByCity(City $city): ?ElectedList
    {
        return $this->createQueryBuilder('el')
            ->andWhere('el.city = :city')
            ->andWhere('el.isActive = :active')
            ->setParameter('city', $city)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve toutes les listes élues avec leurs données complètes
     */
    public function findAllWithCompleteData(): array
    {
        return $this->createQueryBuilder('el')
            ->leftJoin('el.city', 'c')
            ->leftJoin('el.candidateList', 'cl')
            ->leftJoin('el.progressUpdates', 'pu')
            ->leftJoin('pu.commitment', 'cm')
            ->leftJoin('cm.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->addSelect('c', 'cl', 'pu', 'cm', 'p', 'cat')
            ->andWhere('el.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les listes élues pour le mandat actuel
     */
    public function findCurrentMandate(): array
    {
        $currentYear = (int) date('Y');
        
        return $this->createQueryBuilder('el')
            ->leftJoin('el.city', 'c')
            ->leftJoin('el.candidateList', 'cl')
            ->addSelect('c', 'cl')
            ->andWhere('el.mandateStartYear <= :currentYear')
            ->andWhere('el.mandateEndYear >= :currentYear')
            ->andWhere('el.isActive = :active')
            ->setParameter('currentYear', $currentYear)
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une liste élue avec tous ses suivis de progression
     */
    public function findOneWithProgressUpdates(int $id): ?ElectedList
    {
        return $this->createQueryBuilder('el')
            ->leftJoin('el.city', 'c')
            ->leftJoin('el.candidateList', 'cl')
            ->leftJoin('el.progressUpdates', 'pu')
            ->leftJoin('pu.commitment', 'cm')
            ->leftJoin('cm.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->leftJoin('pu.updatedBy', 'u')
            ->addSelect('c', 'cl', 'pu', 'cm', 'p', 'cat', 'u')
            ->andWhere('el.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Statistiques globales des listes élues
     */
    public function getGlobalStats(): array
    {
        $qb = $this->createQueryBuilder('el');
        
        return [
            'totalElectedLists' => $qb->select('COUNT(el.id)')
                ->andWhere('el.isActive = :active')
                ->setParameter('active', true)
                ->getQuery()
                ->getSingleScalarResult(),
                
            'currentMandateLists' => $qb->select('COUNT(el.id)')
                ->andWhere('el.mandateStartYear <= :currentYear')
                ->andWhere('el.mandateEndYear >= :currentYear')
                ->andWhere('el.isActive = :active')
                ->setParameter('currentYear', (int) date('Y'))
                ->setParameter('active', true)
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }

    /**
     * Trouve les listes élues par année d'élection
     */
    public function findByElectionYear(int $year): array
    {
        return $this->createQueryBuilder('el')
            ->leftJoin('el.city', 'c')
            ->leftJoin('el.candidateList', 'cl')
            ->addSelect('c', 'cl')
            ->andWhere('YEAR(el.electionDate) = :year')
            ->andWhere('el.isActive = :active')
            ->setParameter('year', $year)
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de listes élues par nom de maire ou ville
     */
    public function searchByMayorOrCity(string $searchTerm): array
    {
        return $this->createQueryBuilder('el')
            ->leftJoin('el.city', 'c')
            ->leftJoin('el.candidateList', 'cl')
            ->addSelect('c', 'cl')
            ->andWhere('el.mayorName LIKE :search OR c.name LIKE :search')
            ->andWhere('el.isActive = :active')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
