<?php

namespace App\Repository;

use App\Entity\Searchterm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Searchterm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Searchterm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Searchterm[]    findAll()
 * @method Searchterm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchtermRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Searchterm::class);
    }

    public function findByTerm($term) {
        return $this->createQueryBuilder('t')
            ->where('t.term = :term')
            ->setParameter('term', $term)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findMostUsedTerms() {
        return $this->createQueryBuilder('t')
            ->orderBy('t.count')
            ->orderBy('t.latestResultCount')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult();
    }
}
