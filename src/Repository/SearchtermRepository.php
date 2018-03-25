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

//    /**
//     * @return Searchterm[] Returns an array of Searchterm objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Searchterm
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
