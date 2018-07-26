<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Recipe;
use App\Entity\Blog;
use Doctrine\ORM\EntityManagerInterface;

class AdminController extends Controller
{
    private $em;

    public function __construct(EntityManagerInterface $entitiyManager) {
        $this->em = $entitiyManager;
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
     * @Route("/admin/fehlende-bilder", name="missingImages")
     */
    public function missingImages() {
        $blogs = $this->em->getRepository(Blog::class)->findAll();

        usort($blogs, function($a, $b) {
            if (count($a->getRecipes()) <= 0 && count($b->getRecipes()) <= 0) {
                return 0;
            } else if (count($a->getRecipes()) <= 0) {
                return +1;
            } else if (count($b->getRecipes()) <= 0) {
                return -1;
            } else {
                return (count($a->getRecipes()) / count($a->getRecipesWithoutImage())) - ((count($b->getRecipes()) / count($b->getRecipesWithoutImage())));
            }
        });

        return $this->render('admin/missingImages.html.twig', [
            'blogs' => $blogs
        ]);
    }

    /**
     * @Route("/admin/fehlende-bild-dateien", name="missingImageFiles")
     */
    public function missingImageFiles() {
        $blogs = $this->em->getRepository(Blog::class)->findAll();
        $blogsWithMissingImageFiles = [];
        foreach($blogs as $blog) {
            $missingImageFiles = 0;
            foreach($blog->getValidRecipes() as $recipe) {
                if (!file_exists("/images/recipes/" . $recipe->getImage() . ".jpg")) {
                    $missingImageFiles++;
                }
            }
            if ($missingImageFiles > 0) {
                $blogsWithMissingImageFiles[] = array("blogName" => $blog->getTitle(), "missingImageFileCount" => $missingImageFiles);
            }
        }

        usort($blogsWithMissingImageFiles, function($a, $b) { return $b["missingImageFileCount"] - $a["missingImageFileCount"]; });

        return $this->render('admin/missingImageFiles.html.twig', [
            'blogs' => $blogsWithMissingImageFiles
        ]);
    }
}
