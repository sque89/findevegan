<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use App\Entity\Blog;
use App\Entity\Searchterm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Form\ReportRecipeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController {

    private $em;
    private $router;

    public function __construct(EntityManagerInterface $entitiyManager, UrlGeneratorInterface $router) {
        $this->em = $entitiyManager;
        $this->router = $router;
    }

    private function handleUglyTerm($term, $routeToRedirect, $routeParameters = []) {
        if ($term && preg_match("/^[0-9a-zöüäßÖÜÄ\+ ]+$/i", $term)) {
            $term = preg_replace('/\s+/', '+', $term);
            $routeParameters["term"] = $term;
            return $this->redirectToRoute($routeToRedirect, $routeParameters, 301);
        }
        return null;
    }

    private function handleSearchTerm($term) {
        if ($term) {
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
    }

    private function getCategoryList($routeName, $parameters = []) {
        $categories = [];
        foreach ($this->em->getRepository(RecipeCategory::class)->findAll() as $category) {
            $categories[] = array(
                'title' => $category->getTitle(),
                'path' => $this->router->generate($routeName, array_merge(array('categorySlug' => $category->getSlug()), $parameters))
            );
        }
        return $categories;
    }

    private function getPageTextElements($page = 1, $category = null, $blog = null, $term = null) {
        $title = "Die aktuellsten veganen Rezepte";
        $searchfieldPlaceholder = "Suchbegriff z.B. Nudeln";
        $breadcrumb = [];

        if ($category && $blog && $term) {
            $title = sprintf("Vegane Rezepte mit \"%s\" von %s in %s", $term, $blog->getTitle(), $category->getTitle());
            $breadcrumb[] = array("label" => $blog->getTitle(), "url" => $this->generateUrl("blog", array("blogSlug" => $blog->getSlug())));
            $breadcrumb[] = array("label" => $category->getTitle(), "url" => $this->generateUrl(
                        "blogWithCategory", array("blogSlug" => $blog->getSlug(), "categorySlug" => $category->getSlug())
            ));
            $breadcrumb[] = array("label" => '"' . $term . '"', "url" => $this->generateUrl(
                        "blogWithCategoryAndTerm", array("blogSlug" => $blog->getSlug(), "categorySlug" => $category->getSlug(), "term" => $term)
            ));
        } else if ($category && $blog) {
            $title = sprintf("Vegane Rezepte von %s in %s", $blog->getTitle(), $category->getTitle());
            $searchfieldPlaceholder = sprintf("Suche bei %s in %s", $blog->getTitle(), $category->getTitle());
            $breadcrumb[] = array("label" => $blog->getTitle(), "url" => $this->generateUrl("blog", array("blogSlug" => $blog->getSlug())));
            $breadcrumb[] = array("label" => $category->getTitle(), "url" => $this->generateUrl(
                        "blogWithCategory", array("blogSlug" => $blog->getSlug(), "categorySlug" => $category->getSlug())
            ));
        } else if ($blog && $term) {
            $title = sprintf("Vegane Rezepte mit \"%s\" von %s", $term, $blog->getTitle() ?? $category->getTitle());
            $breadcrumb[] = array("label" => $blog->getTitle(), "url" => $this->generateUrl("blog", array("blogSlug" => $blog->getSlug())));
            $breadcrumb[] = array("label" => '"' . $term . '"', "url" => $this->generateUrl(
                        "blogWithTerm", array("blogSlug" => $blog->getSlug(), "term" => $term)
            ));
        } else if ($category && $term) {
            $title = sprintf("Vegane Rezepte mit \"%s\" in %s", $term, $category->getTitle() ?? $category->getTitle());
            $breadcrumb[] = array("label" => $category->getTitle(), "url" => $this->generateUrl("category", array("categorySlug" => $category->getSlug())));
            $breadcrumb[] = array("label" => '"' . $term . '"', "url" => $this->generateUrl(
                        "categoryWithTerm", array("categorySlug" => $category->getSlug(), "term" => $term)
            ));
        } else if ($blog) {
            $title = sprintf("Vegane Rezepte von %s", $blog->getTitle());
            $searchfieldPlaceholder = sprintf("Suche bei %s", $blog->getTitle());
            $breadcrumb[] = array("label" => $blog->getTitle(), "url" => $this->generateUrl("blog", array("blogSlug" => $blog->getSlug())));
        } else if ($category) {
            $title = sprintf("Vegane Rezepte in %s", $category->getTitle());
            $searchfieldPlaceholder = sprintf("Suche in %s", $category->getTitle());
            $breadcrumb[] = array("label" => $category->getTitle(), "url" => $this->generateUrl("category", array("categorySlug" => $category->getSlug())));
        } else if ($term) {
            $title = $headline = sprintf("Vegane Rezepte mit \"%s\"", $term);
            $breadcrumb[] = array("label" => '"' . $term . '"', "url" => $this->generateUrl("latestWithTerm", array("term" => $term)));
        }

        return array("title" => $title, "searchfieldPlaceholder" => $searchfieldPlaceholder, "breadcrumb" => $breadcrumb);
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
        $this->handleSearchTerm($term);

        return $this->handleUglyTerm($request->query->get("q"), 'latestWithTerm') ??
                $this->render('recipe/list.html.twig', [
                    'recipes' => $this->em->getRepository(Recipe::class)->findRecipeListForCriterias($request->query->get("page", 1), null, null, $term),
                    'term' => str_replace('+', ' ', $term),
                    'searchPlaceholder' => 'Suche in allen Rezepten',
                    'categories' => $this->getCategoryList('category'),
                    'pageTextElements' => $this->getPageTextElements($request->query->get("page", 1), null, null, $term)
        ]);
    }

    /**
     * @Route("/rezepte/blog/{blogSlug}", name="blog")
     * @Route("/rezepte/blog/{blogSlug}/kategorie/{categorySlug}", name="blogWithCategory")
     * @Route("/rezepte/blog/{blogSlug}/suche/{term}", name="blogWithTerm")
     * @Route("/rezepte/blog/{blogSlug}/kategorie/{categorySlug}/suche/{term}", name="blogWithCategoryAndTerm")
     */
    public function blog(Request $request, string $blogSlug, string $categorySlug = null, string $term = null) {
        $blog = $this->em->getRepository(Blog::class)->findOneBySlug($blogSlug);
        $category = $this->em->getRepository(RecipeCategory::class)->findOneBySlug($categorySlug);
        $this->handleSearchTerm($term);
        $response = null;

        if ($categorySlug) {
            $response = $this->handleUglyTerm($request->query->get("q"), "blogWithCategoryAndTerm", array("blogSlug" => $blogSlug, "categorySlug" => $categorySlug));
        } else {
            $response = $this->handleUglyTerm($request->query->get("q"), "blogWithTerm", array("blogSlug" => $blogSlug));
        }

        return $response ??
                $this->render('recipe/list.html.twig', [
                    'recipes' => $this->em->getRepository(Recipe::class)->findRecipeListForCriterias($request->query->get("page", 1), $categorySlug, $blogSlug, $term),
                    'term' => str_replace('+', ' ', $term),
                    'searchPlaceholder' => "blub",
                    'categories' => $this->getCategoryList('blogWithCategory', array('blogSlug' => $blog->getSlug())),
                    'pageTextElements' => $this->getPageTextElements($request->query->get("page", 1), $category, $blog, $term)
        ]);
    }

    /**
     * @Route("/rezepte/kategorie/{categorySlug}", name="category")
     * @Route("/rezepte/kategorie/{categorySlug}/suche/{term}", name="categoryWithTerm")
     */
    public function category(Request $request, $categorySlug, $term = null) {
        $category = $this->em->getRepository(RecipeCategory::class)->findOneBySlug($categorySlug);
        $this->handleSearchTerm($term);

        return $this->handleUglyTerm($request->query->get("q"), 'categoryWithTerm', array("categorySlug" => $categorySlug)) ??
                $this->render('recipe/list.html.twig', [
                    'recipes' => $this->em->getRepository(Recipe::class)->findRecipeListForCriterias($request->query->get("page", 1), $categorySlug, null, $term),
                    'term' => str_replace('+', ' ', $term),
                    'searchPlaceholder' => "blub",
                    'categories' => $this->getCategoryList('category'),
                    'pageTextElements' => $this->getPageTextElements($request->query->get("page", 1), $category, null, $term)
        ]);
    }

    /**
     * @Route("/rezept/melden/{recipeId}", name="reportRecipe")
     */
    public function reportRecipe(Request $request, $recipeId = null) {
        $sent = false;
        $success = false;
        $recipe = $this->em->getRepository(Recipe::class)->findOneById($recipeId);
        $form = $this->createForm(ReportRecipeType::class);
        $form->get('recipeId')->setData($recipeId);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sent = true;
            try {
                $reportFormData = $form->getData();
                $recipeToBan = $this->em->getRepository(Recipe::class)->findOneById($reportFormData["recipeId"]);
                $recipeToBan->setBanned(Recipe::$BANNED_OPTIONS["BAN_PROPOSED"]);
                $recipeToBan->setBanReason($reportFormData["reason"]);
                $this->em->flush();
                $success = true;
            } catch (\Exception $exception) {
                $success = false;
            }
        }

        return $this->render('recipe/report.html.twig', [
            'pageTextElements' => array(
                'title' => 'Rezept melden',
                'breadcrumb' => array(
                    array('label' => 'Rezept melden', 'url' => null),
                    array('label' => $recipe ? $recipe->getBlog()->getTitle() . ": " . $recipe->getTitle() : 'nicht gefunden', 'url' => $this->generateUrl('reportRecipe', array('recipeId' => $recipeId)))
                )
            ),
            'form' => $form->createView(),
            'sent' => $sent,
            'success' => $success,
            'found' => $recipe,
            'recipe' => $recipe
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
