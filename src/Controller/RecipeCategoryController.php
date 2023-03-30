<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\RecipeCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeCategoryController extends AbstractController {
    private $em;

    public function __construct(EntityManagerInterface $entitiyManager) {
        $this->em = $entitiyManager;
    }
}
