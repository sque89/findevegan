<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipeCategoryAlternativeRepository")
 */
class RecipeCategoryAlternative
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $slug;

    /**
     * Many RecipeCategoryAlternatives have One RecipeCategory.
     * @ORM\ManyToOne(targetEntity="RecipeCategory", inversedBy="recipeCategoryAlternatives")
     * @ORM\JoinColumn(name="recipe_category_id", referencedColumnName="id")
     */
    private $recipeCategory;

    public function getId()
    {
        return $this->id;
    }

    public function getSlug(): ?string {
        return $this->slug;
    }

    public function setSlug(string $slug): self {
        $this->slug = $slug;
        return $this;
    }
}
