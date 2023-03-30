<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

/**
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Recipe::class);
    }

    private function getBasicQueryBuilder() {
        return $this->createQueryBuilder('r')
            ->join('r.blog', 'b')
            ->leftJoin('r.categories', 'c')
            //->where('r.imageHasFace = 0')
            ->andWhere('b.enabled = 1')
            ->andWhere('r.banned != ' . Recipe::$BANNED_OPTIONS["BANNED"])
            ->andWhere('r.image IS NOT NULL')
            ->orderBy('r.released', 'DESC');
    }

    private function getPaginator($queryBuilder, $currentPage) {
        $adapter = new QueryAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage(40);
        $paginator->setCurrentPage($currentPage);
        return $paginator;
    }

    public function findLatest($page) {
        return $this->getPaginator($this->createQueryBuilder('r')->where('r.enabled = true'), $page);
    }

    public function findByTerm($term, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->andWhere('r.title LIKE :term')
                ->setParameter(':term', '%'.$term.'%'),
            $page
        );
    }

    public function findByBlogSlug($slug, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->andWhere('b.slug = :slug')
                ->andWhere('b.enabled = 1')
                ->setParameter(':slug', $slug),
            $page
        );
    }

    public function findByBlogSlugAndCategorySlug($slug, $categorySlug, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->andWhere('c.slug = :categorySlug')
                ->andWhere('b.slug = :slug')
                ->andWhere('b.enabled = 1')
                ->setParameter(':slug', $slug)
                ->setParameter(':categorySlug', $categorySlug),
            $page
        );
    }

    public function findByBlogSlugAndTerm($slug, $term, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->andWhere('b.slug = :slug')
                ->andWhere('r.title LIKE :term')
                ->setParameter(':slug', $slug)
                ->setParameter(':term', '%'.$term.'%'),
            $page);
    }

    public function findByBlogSlugAndCategorySlugAndTerm($slug, $categorySlug, $term, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->andWhere('c.slug = :categorySlug')
                ->andWhere('b.slug = :slug')
                ->andWhere('r.title LIKE :term')
                ->setParameter(':slug', $slug)
                ->setParameter(':term', '%'.$term.'%')
                ->setParameter(':categorySlug', $categorySlug),
            $page);
    }

    public function findByCategorySlug($slug, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->andWhere('c.slug = :slug')
                ->setParameter(':slug', $slug),
            $page);
    }

    public function findByCategorySlugAndTerm($slug, $term, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->andWhere('c.slug = :slug')
                ->andWhere('r.title LIKE :term')
                ->setParameter(':slug', $slug)
                ->setParameter(':term', '%'.$term.'%'),
            $page);
    }

    public function findByBannedStatusIsPending() {
        return $this->getBasicQueryBuilder()
            ->where('r.banned = ' . Recipe::$BANNED_OPTIONS['BAN_PROPOSED'])
            ->getQuery()
            ->getResult();
    }

    public function findRecipeListForCriterias($page = 1, $categorySlug = null, $blogSlug = null, $term = null) {
        $queryBuilder = $this->getBasicQueryBuilder();
        if ($categorySlug) {
            $queryBuilder->andWhere('c.slug = :categorySlug');
            $queryBuilder->setParameter('categorySlug', $categorySlug);
        }
        if ($blogSlug) {
            $queryBuilder->andWhere('b.slug = :blogSlug');
            $queryBuilder->setParameter('blogSlug', $blogSlug);
        }
        if ($term) {
            $terms = explode('+', $term);

            foreach ($terms as $key => $term) {
                $queryBuilder->andWhere('r.title LIKE :term' . $key);
                $queryBuilder->setParameter('term' . $key, '%' . $term. '%');
            }
        }
        return $this->getPaginator($queryBuilder, $page);
    }

    public function findNumberOfRecipes() {
        return $this->getBasicQueryBuilder()
            ->select('count(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findNumberOfRecpiesByTerm($term) {
        return $this->getBasicQueryBuilder()
            ->select('count(r.id)')
            ->where('r.title LIKE :term')
            ->setParameter(':term', '%'.$term.'%')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
