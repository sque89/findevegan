<?php

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Blog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blog[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Blog::class);
    }

    private function getBasicQueryBuilder() {
        return $this->createQueryBuilder('b')
            ->join('b.recipes', 'r')
            ->where('b.enabled = 1')
            ->andWhere('r.image IS NOT NULL')
            ->groupBy('b.id')
            ->having('count(r.id) > 0');
    }

    public function findAll($sort = null, $order = null) {
        $basicQuery = $this->getBasicQueryBuilder();

        if ($sort) {
            $basicQuery->orderBy('b.' . $sort, $order);
        }

        return $basicQuery->getQuery()->getResult();
    }

    public function findRange($from, $to) {
        return $this->getBasicQueryBuilder()
            ->andWhere('b.id >= ' . $from)
            ->andWhere('b.id <= ' . $to)
            ->orderBy('RAND()')
            ->getQuery()
            ->getResult();
    }

    public function findBlogsByFirstLetter(string $letter) {
        $basicQuery = $this->getBasicQueryBuilder();

        return $this->getBasicQueryBuilder()
            ->andWhere($basicQuery->expr()->eq($basicQuery->expr()->lower($basicQuery->expr()->substring('b.title', 1, 1)), ':letter'))
            ->setParameter('letter', $letter)
            ->orderBy('b.title')
            ->getQuery()
            ->getResult();
    }

    public function findNumberOfBlogs() {
        $blogs = $this->getBasicQueryBuilder()
            ->getQuery()
            ->getArrayResult();

        return count($blogs);
    }

    public function findMostActiveBlogs() {
        return $this->getBasicQueryBuilder()
            ->select('count(r.id) as count, b.title, b.slug')
            ->andWhere('DATE_DIFF(CURRENT_TIMESTAMP(), r.released) < 30')
            ->orderBy('count', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findUsedCategoriesByBlogId($blogId) {
        return $this->getBasicQueryBuilder()
            ->select('c.title, c.slug')
            ->join('r.categories', 'c')
            ->distinct('c.id')
            ->andWhere('b.id = :blogId')
            ->setParameter('blogId', $blogId)
            ->getQuery()
            ->getResult();
    }
}
