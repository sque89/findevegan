<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use App\Entity\Blog;
use App\Entity\Searchterm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RecipeController extends Controller {

    private $em;
    private $router;

    public function __construct(EntityManagerInterface $entitiyManager, UrlGeneratorInterface $router) {
        $this->em = $entitiyManager;
        $this->router = $router;
    }

    private function handleSearchTerm($term) {
        $alreadyExistingTerm = $this->em->getRepository(Searchterm::class)->findByTerm(mb_strtolower($term));
        if ($alreadyExistingTerm) {
            $alreadyExistingTerm->setCount($alreadyExistingTerm->getCount() + 1);
            $alreadyExistingTerm->setLatestSearch(new \DateTimeImmutable());
            $alreadyExistingTerm->setLatestResultCount($this->em->getRepository(Recipe::class)->findNumberOfRecpiesByTerm($term));
        } else {
            $newTerm = new Searchterm();
            $newTerm->setTerm(mb_strtolower($term));
            $newTerm->setFirstSearch(new \DateTimeImmutable());
            $newTerm->setLatestSearch(new \DateTimeImmutable());
            $newTerm->setLatestResultCount($this->em->getRepository(Recipe::class)->findNumberOfRecpiesByTerm($term));
            $newTerm->setCount(1);
            $this->em->persist($newTerm);
        }
        $this->em->flush();
    }

    private function getCategoryList($routeName, $parameters = []) {
        $categories = [];
        foreach($this->em->getRepository(RecipeCategory::class)->findAll() as $category) {
            $categories[] = array(
                'title' => $category->getTitle(),
                'path' => $this->router->generate($routeName, array_merge(array('categorySlug' => $category->getSlug()), $parameters))
            );
        }
        return $categories;
    }

    /**
     * @Route("/", name="index")
     */
    public function index() {
        return $this->redirectToRoute('latest', [], 301);
    }

    /**
     * @Route("/rezepte/", name="latest")
     * @Route("/rezepte/suche/{term}", name="latestWithTerm")
     */
    public function latest(Request $request, string $term = null) {
        $recipePaginator = null;
        $uglyTerm = $request->query->get("q");

        if ($uglyTerm !== null && preg_match("/^[0-9a-zöüäßÖÜÄ\+]+$/i", $uglyTerm)) {
            return $this->redirectToRoute('latestWithTerm', array("term" => $uglyTerm), 301);
        }

        if ($term) {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findByTerm($term, $request->query->get("page", 1));
            $this->handleSearchTerm($term);
        } else {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findLatest($request->query->get("page", 1));
        }

        return $this->render('recipe/list.html.twig', [
                    'recipes' => $recipePaginator,
                    'term' => $term,
                    'searchPlaceholder' => 'Suche in allen Rezepten',
                    'categories' => $this->getCategoryList('category')
        ]);
    }

    /**
     * @Route("/rezepte/blog/{slug}", name="blog")
     * @Route("/rezepte/blog/{slug}/kategorie/{categorySlug}", name="blogWithCategory")
     * @Route("/rezepte/blog/{slug}/suche/{term}", name="blogWithTerm")
     * @Route("/rezepte/blog/{slug}/kategorie/{categorySlug}/suche/{term}", name="blogWithCategoryAndTerm")
     */
    public function blog(Request $request, string $slug, string $categorySlug = null, string $term = null) {
        $recipePaginator = null;
        $uglyTerm = $request->query->get("q");
        $blog = $this->em->getRepository(Blog::class)->findOneBySlug($slug);
        $category = $this->em->getRepository(RecipeCategory::class)->findOneBySlug($categorySlug);
        $searchPlaceholder = "";

        if ($uglyTerm !== null && preg_match("/^[0-9a-zöüäßÖÜÄ\+]+$/i", $uglyTerm)) {
            if ($categorySlug) {
                return $this->redirectToRoute('blogWithCategoryAndTerm', array("slug" => $slug, "categorySlug" => $categorySlug, "term" => $uglyTerm), 301);
            } else {
                return $this->redirectToRoute('blogWithTerm', array("slug" => $slug, "term" => $uglyTerm), 301);
            }
        }

        if ($term) {
            if ($categorySlug) {
                $recipePaginator = $this->em->getRepository(Recipe::class)->findByBlogSlugAndCategorySlugAndTerm($slug, $categorySlug, $term, $request->query->get("page", 1));
            } else {
                $recipePaginator = $this->em->getRepository(Recipe::class)->findByBlogSlugAndTerm($slug, $term, $request->query->get("page", 1));
            }
            $this->handleSearchTerm($term);
        } else {
            if ($categorySlug) {
                $recipePaginator = $this->em->getRepository(Recipe::class)->findByBlogSlugAndCategorySlug($slug, $categorySlug, $request->query->get("page", 1));
                $searchPlaceholder = "Suche in " . $blog->getTitle() . " // " . $category->getTitle();
            } else {
                $recipePaginator = $this->em->getRepository(Recipe::class)->findByBlogSlug($slug, $request->query->get("page", 1));
                $searchPlaceholder = "Suche in " . $blog->getTitle();
            }
        }

        return $this->render('recipe/list.html.twig', [
                    'recipes' => $recipePaginator,
                    'term' => $term,
                    'searchPlaceholder' => $searchPlaceholder,
                    'categories' => $this->getCategoryList('blogWithCategory', array('slug' => $blog->getSlug()))
        ]);
    }

    /**
     * @Route("/rezepte/kategorie/{categorySlug}", name="category")
     * @Route("/rezepte/kategorie/{categorySlug}/suche/{term}", name="categoryWithTerm")
     */
    public function category(Request $request, $categorySlug, $term = null) {
        $recipePaginator = null;
        $uglyTerm = $request->query->get("q");
        $category = $this->em->getRepository(RecipeCategory::class)->findOneBySlug($categorySlug);
        $searchPlaceholder = "";

        if ($uglyTerm !== null && preg_match("/^[0-9a-zöüäßÖÜÄ\+]+$/i", $uglyTerm)) {
            return $this->redirectToRoute('categoryWithTerm', array("slug" => $categorySlug, "term" => $uglyTerm), 301);
        }

        if ($term) {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findByCategorySlugAndTerm($categorySlug, $term, $request->query->get("page", 1));
            $this->handleSearchTerm($term);
        } else {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findByCategorySlug($categorySlug, $request->query->get("page", 1));
            $searchPlaceholder = "Suche in " . $category->getTitle();
        }

        return $this->render('recipe/list.html.twig', [
                    'recipes' => $recipePaginator,
                    'term' => $term,
                    'searchPlaceholder' => $searchPlaceholder,
                    'categories' => $this->getCategoryList('category')
        ]);
    }

    /**
     * Rendert das Statistik Widget
     * @return type
     */
    public function statistic() {
        $numberOfRecipes = $this->em->getRepository(Recipe::class)->findNumberOfRecipes();
        $numberOfBlogs = $this->em->getRepository(Blog::class)->findNumberOfBlogs();
        return $this->render(
                        'recipe/statistic.html.twig', array('numberOfRecipes' => $numberOfRecipes, 'numberOfBlogs' => $numberOfBlogs)
        );
    }

    /**
     * Rendert das Termcloud Widget
     * @return type
     */
    public function termCloud() {
        $mostUsedTerms = $this->em->getRepository(Searchterm::class)->findMostUsedTerms();
        $mostUsedTermsForTemplate = [];
        foreach ($mostUsedTerms as $key => $value) {
            $mostUsedTermsForTemplate[] = array("term" => $value, "level" => intdiv($key, 5));
        }
        shuffle($mostUsedTermsForTemplate);
        return $this->render(
                        'recipe/termcloud.html.twig', array('mostUsedTerms' => $mostUsedTermsForTemplate)
        );
    }

}
