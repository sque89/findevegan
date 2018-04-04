<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Recipe;
use Symfony\Component\HttpFoundation\Request;

class RecipeController extends Controller {
    /**
     * @Route("/", name="latest")
     */
    public function latest(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('recipe/list.html.twig', [
            'controller_name' => 'RecipeController',
            'recipes' => $entityManager->getRepository(Recipe::class)->findLatest($request->query->get("page", 1))
        ]);
    }

    /**
     * @Route("/blog/{slug}", name="blog")
     */
    public function blog($slug, EntityManagerInterface $entityManager)
    {
        return $this->render('recipe/list.html.twig', [
            'controller_name' => 'RecipeController',
            'recipes' => $entityManager->getRepository(Recipe::class)->findByBlogSlug($slug)
        ]);
    }
}
