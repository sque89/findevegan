<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Recipe::class);
    }

    public function findLatest($page) {
        $queryBuilder = $this->createQueryBuilder('r')->where('r.enabled = true');
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage(40);
        $paginator->setCurrentPage($page);
        return $paginator;
    }

    public function findByBlogSlug($slug) {
        return $this->createQueryBuilder('r')
                        ->join('r.blog', 'b')
                        ->where('b.slug = :slug')
                        ->setMaxResults(20)
                        ->setParameter(':slug', $slug)
                        ->getQuery()
                        ->getResult();
    }

    public function findOneByPermalink($permalink): ?Recipe {
        return $this->createQueryBuilder('r')
                        ->andWhere('r.permalink = :val')
                        ->setParameter('val', $permalink)
                        ->getQuery()
                        ->getOneOrNullResult();
    }
}
