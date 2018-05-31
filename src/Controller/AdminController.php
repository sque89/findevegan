<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Recipe;
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
            'recipes' => $reportedRecipes,
        ]);
    }
}
