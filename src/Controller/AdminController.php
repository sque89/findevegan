<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Recipe;
use App\Entity\Blog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController {

    private $em;

    public function __construct(EntityManagerInterface $entitiyManager) {
        $this->em = $entitiyManager;
    }

    private function getMissingImageFileCountForBlog($blog) {
        $missingImageFiles = 0;
        foreach ($blog->getValidRecipes() as $recipe) {
            if (!file_exists("images/recipes/" . $recipe->getImage() . ".jpg")) {
                $missingImageFiles++;
            }
        }
        return $missingImageFiles;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index() {
        return $this->redirectToRoute('blogList');
    }

    /**
     * @Route("/admin/blogliste/{sortColumn}/{order}", name="blogList")
     */
    public function blogList($sortColumn = 'id', $order = 'asc') {
        $blogs = $this->em->getRepository(Blog::class)->findAll();
        $blogList = [];
        $missingImageFileCount = null;

        foreach ($blogs as $blog) {
            $missingImageFileCount = $this->getMissingImageFileCountForBlog($blog);
            $blogList[] = array(
                "blog" => $blog,
                "missingImageFileCount" => $missingImageFileCount,
                "crawlSuccess" => $blog->getLatestSuccessfulCrawl() && $blog->getLatestSuccessfulCrawl()->diff(new \DateTimeImmutable())->d <= 2,
                "percentageWithoutImage" => count($blog->getRecipes()) > 0 ? count($blog->getRecipesWithoutImage()) / count($blog->getRecipes()) * 100 : 0,
                "percentageWithoutImageFile" => $missingImageFileCount > 0 ? $missingImageFileCount / count($blog->getValidRecipes()) * 100 : 0
            );
        }

        usort($blogList, function ($a, $b) use ($sortColumn, $order) {
            switch ($sortColumn) {
                case 'id': return ($order === 'asc' ? 1 : -1) * ($a["blog"]->getId() - $b["blog"]->getId());
                    break;
                case 'type': return ($order === 'asc' ? 1 : -1) * strcasecmp($a["blog"]->getType(), $b["blog"]->getType());
                    break;
                case 'title': return ($order === 'asc' ? 1 : -1) * strcasecmp($a["blog"]->getTitle(), $b["blog"]->getTitle());
                    break;
                case 'recipeCount': return ($order === 'asc' ? 1 : -1) * (count($a["blog"]->getRecipes()) - count($b["blog"]->getRecipes()));
                    break;
                case 'withoutImage': return ($order === 'asc' ? 1 : -1) * (count($a["blog"]->getRecipesWithoutImage()) - count($b["blog"]->getRecipesWithoutImage()));
                    break;
                case 'withoutImageFile': return ($order === 'asc' ? 1 : -1) * ($a["missingImageFileCount"] - $b["missingImageFileCount"]);
                    break;
                case 'withoutImagePercentage': return ($order === 'asc' ? 1 : -1) * ($a["percentageWithoutImage"] - $b["percentageWithoutImage"]);
                    break;
                case 'withoutImageFilePercentage': return ($order === 'asc' ? 1 : -1) * ($a["percentageWithoutImageFile"] - $b["percentageWithoutImageFile"]);
                    break;
                case 'crawlSuccess':
                    if ($a["blog"]->getLatestSuccessfulCrawl() === null) {
                        return ($order === 'asc' ? 1 : -1) * -1;
                    } else if ($b["blog"]->getLatestSuccessfulCrawl() === null) {
                        return ($order === 'asc' ? 1 : -1) * 1;
                    } else {
                        return ($order === 'asc' ? 1 : -1) * ($a["blog"]->getLatestSuccessfulCrawl() <=> $b["blog"]->getLatestSuccessfulCrawl());
                    }
                    break;
            }
        });

        return $this->render('admin/blogList.html.twig', [
                    'blogs' => $blogList,
                    'currentSort' => $sortColumn,
                    'currentOrder' => $order,
        ]);
    }

    /**
     * @Route("/admin/meldungen", name="reportings")
     * @Route("/admin/meldungen/bestaetigen/{approveRecipeId}", name="reportingsApprove")
     * @Route("/admin/meldungen/verweigern/{denyRecipeId}", name="reportingsDeny")
     */
    public function reportings($approveRecipeId = null, $denyRecipeId = null) {
        if ($approveRecipeId !== null) {
            $recipeToApprove = $this->em->getRepository(Recipe::class)->findOneById($approveRecipeId);
            $recipeToApprove->setBanned(Recipe::$BANNED_OPTIONS["BANNED"]);
            $this->em->flush();
        } else if ($denyRecipeId !== null) {
            $recipeToDeny = $this->em->getRepository(Recipe::class)->findOneById($denyRecipeId);
            $recipeToDeny->setBanned(Recipe::$BANNED_OPTIONS["UNBANNED"]);
            $this->em->flush();
        }

        $reportedRecipes = $this->em->getRepository(Recipe::class)->findByBannedStatusIsPending();

        return $this->render('admin/reportings.html.twig', [
                    'recipes' => $reportedRecipes
        ]);
    }

    /**
     * @Route("/admin/disable/{id}", name="blogDisable")
     */
    public function blogDisable($id) {
        $blog = $this->em->getRepository(Blog::class)->findOneById($id);
        $blog->setEnabled(false);
        $this->em->flush();
        return $this->redirectToRoute('blogList');
    }

    /**
     * @Route("/admin/enable/{id}", name="blogEnable")
     */
    public function blogEnable($id) {
        $blog = $this->em->getRepository(Blog::class)->findOneById($id);
        $blog->setEnabled(true);
        $this->em->flush();
        return $this->redirectToRoute('blogList');
    }
}
