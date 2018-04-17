<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\RecipeCategory;

class RecipeCategoryController extends Controller {
    private $em;

    public function __construct(EntityManagerInterface $entitiyManager) {
        $this->em = $entitiyManager;
    }
}
