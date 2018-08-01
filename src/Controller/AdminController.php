<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Recipe;
use App\Entity\Blog;
use Doctrine\ORM\EntityManagerInterface;

class AdminController extends Controller {

    private $em;

    public function __construct(EntityManagerInterface $entitiyManager) {
        $this->em = $entitiyManager;
    }

    private function getMissingImageFilesForBlog($blog) {
        $missingImageFiles = 0;
        foreach ($blog->getValidRecipes() as $recipe) {
            if (!file_exists("images/recipes/" . $recipe->getImage() . ".jpg")) {
                $missingImageFiles++;
            }
        }
        return $missingImageFiles;
    }

    /**
     * @Route("/admin", name="blogList")
     */
    public function blogList() {
        $blogs = $this->em->getRepository(Blog::class)->findAll();
        $blogList = [];

        foreach($blogs as $blog) {
            $blogList[] = array(
                "blog" => $blog,
                "missingImageFileCount" => $this->getMissingImageFilesForBlog($blog),
                "crawlSuccess" => $blog->getLatestSuccessfulCrawl() && $blog->getLatestSuccessfulCrawl()->diff(new \DateTimeImmutable())->d <= 2);
        }

        return $this->render('admin/blogList.html.twig', [
            'blogs' => $blogList
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
}
