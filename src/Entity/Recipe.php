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
     * @ORM\Column(type="datetime")
     */
    private $released;

    /**
     * @ORM\Column(type="datetime")
     */
    private $crawled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasThumbnail;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $thumbnailOrientation;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $edited;

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

    public function getHasThumbnail(): ?bool
    {
        return $this->hasThumbnail;
    }

    public function setHasThumbnail(bool $hasThumbnail): self
    {
        $this->hasThumbnail = $hasThumbnail;

        return $this;
    }

    public function getThumbnailOrientation(): ?string
    {
        return $this->thumbnailOrientation;
    }

    public function setThumbnailOrientation(string $thumbnailOrientation): self
    {
        $this->thumbnailOrientation = $thumbnailOrientation;

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

    public function getEdited(): ?\DateTimeImmutable
    {
        return $this->edited;
    }

    public function setEdited(\DateTimeImmutable $edited): self
    {
        $this->edited = $edited;

        return $this;
    }
}
