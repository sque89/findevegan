<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Recipe;

class RecipeController extends Controller {
    /**
     * @Route("/", name="latest")
     */
    public function latest(EntityManagerInterface $entityManager)
    {
        return $this->render('recipe/list.html.twig', [
            'controller_name' => 'RecipeController',
            'recipes' => $entityManager->getRepository(Recipe::class)->findLatest()
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
