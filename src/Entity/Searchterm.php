<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearchtermRepository")
 */
class Searchterm
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $term;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $firstSearch;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $latestSearch;

    /**
     * @ORM\Column(type="integer")
     */
    private $latestResultCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $count;

    public function getId()
    {
        return $this->id;
    }

    public function getTerm(): ?string {
        return $this->term;
    }

    public function setTerm(string $term): self
    {
        $this->term = $term;

        return $this;
    }

    public function getFirstSearch(): ?\DateTimeImmutable
    {
        return $this->firstSearch;
    }

    public function setFirstSearch(\DateTimeImmutable $firstSearch): self
    {
        $this->firstSearch = $firstSearch;

        return $this;
    }

    public function getLatestSearch(): ?\DateTimeImmutable
    {
        return $this->latestSearch;
    }

    public function setLatestSearch(\DateTimeImmutable $latestSearch): self
    {
        $this->latestSearch = $latestSearch;

        return $this;
    }

    public function getLatestResultCount() {
        return $this->latestResultCount;
    }

    public function setLatestResultCount($latestResultCount) : self {
        $this->latestResultCount = $latestResultCount;

        return $this;
    }

    public function getCount() {
        return $this->count;
    }

    public function setCount($count) : self {
        $this->count = $count;

        return $this;
    }
}
