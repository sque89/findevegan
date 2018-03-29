<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use \App\Repository\RecipeCategoryRepository;
use \claviska\SimpleImage;

class CrawlService {

    private $IMAGE_PATTERN = "/https?:\/\/[^\/\s]+\/\S+\.(jpg|png)|https?:\/\/[^\/\s]+\/\S+\"/";
    private $INVALID_PATH_SEGMENTS = [
        "comment",
        "smilie",
        "gravatar",
        "trenner",
        "feed",
        "emoji",
        "facebook",
        "pinterest",
        "simple-share-buttons-adder",
        "pixel",
        "banner"
    ];

    public function parseRecipeCategories(RecipeCategoryRepository $repository, Crawler $categoryNodes): Array {
        $recipeCategories = [];
        $categoryNodes->each(function(Crawler $categoryNode) use ($repository, &$recipeCategories) {
            $category = $repository->findOneBySlugOrAlternative($categoryNode->text());
            if ($category) {
                $recipeCategories[] = $category;
            }
        });
        return $recipeCategories;
    }

    public function parseImage(string $nodeContent) {
        $matches = [];
        preg_match_all($this->IMAGE_PATTERN, $nodeContent, $matches);

        $image = null;
        $name = $this->generateImageId();
        foreach ($matches[0] as $match) {
            if (!$this->pathIsInvalidBecauseOfPathSegment($match)) {
                try {
                    $image = new SimpleImage($match);
                    if ($image->getWidth() >= 400 || $image->getHeight() >= 400) {
                        $image->getOrientation() === 'landscape' ? $image->resize(400, null) : $image->resize(null, 400);
                        $image->toFile("images/" . $name . ".jpg", "image/jpeg", 80);
                        return [
                            "name" => $name,
                            "orientation" => $image->getOrientation()
                        ];
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    public function parseReleaseDate(Crawler $itemNode): \DateTime {
        $releasedDate = null;
        if ($itemNode->filter("published")->count() >= 1) {
            $releasedDate = new \DateTime($itemNode->filter("published")->first()->text());
        } else if ($itemNode->filter("pubDate")->count() >= 1) {
            $releasedDate = new \DateTime($itemNode->filter("pubDate")->first()->text());
        }
        return $releasedDate;
    }

    private function pathIsInvalidBecauseOfPathSegment($url) {
        foreach ($this->INVALID_PATH_SEGMENTS as $segment) {
            return stripos($url, $segment);
        }
        return false;
    }

    private function generateImageId($length = 16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}
