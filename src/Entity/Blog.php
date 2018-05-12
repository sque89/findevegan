<?php

namespace App\Entity;

use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use Symfony\Component\DomCrawler\Crawler;
use App\Service\CrawlService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BlogRepository")
 */
class Blog
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
    private $type;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $feed;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $raw;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * One Blog has Many Recipes.
     * @ORM\OneToMany(targetEntity="Recipe", mappedBy="blog")
     */
    private $recipes;

    /**
     * One Blog has Many Books.
     * @ORM\OneToMany(targetEntity="Book", mappedBy="blog")
     */
    private $books;

    public function getId()
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getFeed(): ?string
    {
        return $this->feed;
    }

    public function setFeed(string $feed): self
    {
        $this->feed = $feed;

        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRaw(): ?bool
    {
        return $this->raw;
    }

    public function setRaw(bool $raw): self
    {
        $this->raw = $raw;

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

    public function getRecipes() {
        return $this->recipes;
    }

    public function getEnabledRecipes() {
        return $this->getRecipes()->filter(function (Recipe $recipe) {
            return $recipe->getEnabled();
        });
    }

    public function setRecipes($recipes): self {
        $this->recipes = $recipes;
        return $this;
    }
}
