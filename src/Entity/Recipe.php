<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipeRepository")
 */
class Recipe
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $permalink;

    /**
     * @ORM\Column(type="string", length=16, nullable=true, unique=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $imageOrientation;

    /**
     * Many Recipes have Many RecipeCategories.
     * @ORM\ManyToMany(targetEntity="RecipeCategory")
     * @ORM\JoinTable(name="recipes_recipe_categories",
     *      joinColumns={@ORM\JoinColumn(name="recipe_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="recipe_catetory_id", referencedColumnName="id")}
     *      )
     */
    private $categories;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="datetime")
     */
    private $released;

    /**
     * @ORM\Column(type="datetime")
     */
    private $crawled;

    /**
     * @ORM\ManyToOne(targetEntity="Blog")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id")
     */
    private $blog;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPermalink(): ?string
    {
        return $this->permalink;
    }

    public function setPermalink(string $permalink): self
    {
        $this->permalink = $permalink;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image = null): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageOrientation(): ?string
    {
        return $this->imageOrientation;
    }

    public function setImageOrientation(string $imageOrientation): self
    {
        $this->imageOrientation = $imageOrientation;

        return $this;
    }

    public function getCategories(): ?Array {
        return $this->categories;
    }

    public function setCategories(Array $categories): self {
        $this->categories = $categories;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getReleased(): ?\DateTimeInterface
    {
        return $this->released;
    }

    public function setReleased(\DateTimeInterface $released): self
    {
        $this->released = $released;

        return $this;
    }

    public function getCrawled(): ?\DateTimeInterface
    {
        return $this->crawled;
    }

    public function setCrawled(\DateTimeInterface $crawled): self
    {
        $this->crawled = $crawled;

        return $this;
    }
}
