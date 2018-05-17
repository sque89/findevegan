<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use App\Repository\RecipeCategoryRepository;
use App\Entity\Recipe;
use App\Entity\Blog;
use claviska\SimpleImage;

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
    private $recipeCategoryRepository;
    private $faceDetector;

    public function __construct(RecipeCategoryRepository $recipeCategoryRepository) {
        $this->recipeCategoryRepository = $recipeCategoryRepository;
        $this->faceDetector = new \svay\FaceDetector("detection.dat");
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

    private function categoryAlreadySetForRecipe($categoryToCheck, $alreadySetCategories) {
        $alreadySet = false;
        foreach ($alreadySetCategories as $category) {
            if ($categoryToCheck->getId() === $category->getId()) {
                $alreadySet = true;
            }
        }
        return $alreadySet;
    }

    private function checkAndStoreImage($path) {
        $name = $this->generateImageId();
        $returnValue = array(
            "name" => $name,
            "hasFace" => false
        );
        $originImagePath = "images/recipes/temp/org_" . $name . ".jpg";
        $thumbImagePath = "images/recipes/" . $name . ".jpg";
        $image = new SimpleImage($path);
        if ($image->getWidth() >= 400) {
            $image->toFile($originImagePath, "image/jpeg", 100);
            $image->thumbnail(400, 320);
            $image->toFile($thumbImagePath, "image/jpeg", 80);
            $imageHasFace = $this->faceDetector->faceDetect($originImagePath) || $this->faceDetector->faceDetect($thumbImagePath);

            if ($imageHasFace) {
                $returnValue["hasFace"] = true;
            }
        }
        unlink($originImagePath);
        return $returnValue;
    }

    public function fetchRecipe(Crawler $recipeNode, Blog $blog) {
        $recipe = new Recipe();
        $imageData = $this->parseImage($recipeNode->text());
        $recipe->setTitle($this->parseTitle($recipeNode));
        $recipe->setPermalink($this->parsePermalink($recipeNode));
        $recipe->setReleased($this->parseReleaseDate($recipeNode));
        $recipe->setCategories($this->parseRecipeCategories($recipeNode->filter('category')));
        $recipe->setImage($imageData["name"]);
        $recipe->setImageHasFace($imageData["hasFace"]);
        $recipe->setEnabled(true);
        $recipe->setCrawled(new \DateTime("now"));
        $recipe->setBlog($blog);
        return $recipe;
    }

    public function parseRecipeCategories(Crawler $categoryNodes): Array {
        $recipeCategories = [];
        $categoryNodes->each(function(Crawler $categoryNode) use (&$recipeCategories) {
            $category = $this->recipeCategoryRepository->findOneBySlugOrAlternative(strtolower($categoryNode->text()));
            if ($category && !$this->categoryAlreadySetForRecipe($category, $recipeCategories)) {
                $recipeCategories[] = $category;
            }
        });
        return $recipeCategories;
    }

    public function parseTitle(Crawler $recipeNode) {
        if ($recipeNode->filter('title')->count() > 0) {
            return $recipeNode->filter('title')->first()->text();
        } else if ($recipeNode->filterXPath('//default:title')->count() > 0) {
            return $recipeNode->filterXPath('//default:title')->first()->text();
        }
    }

    public function parsePermalink(Crawler $recipeNode) {
        $link = null;

        if ($recipeNode->filter('link')->count() > 0) {
            $link = $recipeNode->filter('link')->first()->text();
        } else if ($recipeNode->filterXPath("//default:link")->count() == 1) {
            $link = $recipeNode->filterXPath("//default:link")->first()->text();
        } else if ($recipeNode->filterXPath("//default:link")->count() > 1 && $recipeNode->filterXPath("//default:link[@rel='alternate']")->count() == 1) {
            echo $recipeNode->filterXPath("//default:link[@rel='alternate']")->first()->text();
            $link = $recipeNode->filterXPath("//default:link[@rel='alternate']")->first()->attr("href");
        }

        if ($link) {
            $link = str_replace("http://", "", $link);
            $link = str_replace("https://", "", $link);
            $link = str_replace("www.", "", $link);
            $link = preg_replace('/\?.*/', '', $link);
            return $link;
        } else {
            return null;
        }
    }

    public function parseImage(string $nodeContent) {
        $matches = [];
        preg_match_all($this->IMAGE_PATTERN, $nodeContent, $matches);

        foreach ($matches[0] as $match) {
            if (!$this->pathIsInvalidBecauseOfPathSegment($match)) {
                try {
                    return $this->checkAndStoreImage($match);
                } catch (\Exception $e) {
                    //echo $e->getMessage(). "<br />";
                    try {
                        return $this->checkAndStoreImage(str_replace("https", "http", $match));
                    } catch (\Exception $ex) {
                        //echo $e->getMessage(). "<br />";
                        continue;
                    }
                }
            }
        }

        return array(
            "name" => null,
            "hasFace" => false
        );
    }

    public function parseReleaseDate(Crawler $itemNode): \DateTime {
        $releasedDate = null;
        if ($itemNode->filterXPath("//published")->count() >= 1) {
            $releasedDate = new \DateTime($itemNode->filter("//published")->first()->text());
        } else if ($itemNode->filterXPath("//pubDate")->count() >= 1) {
            $releasedDate = new \DateTime($itemNode->filterXPath("//pubDate")->first()->text());
        } else if ($itemNode->filterXPath("//default:published")->count() >= 1) {
            $releasedDate = new \DateTime($itemNode->filterXPath("//default:published")->first()->text());
        } else if ($itemNode->filterXPath("//default:pubDate")->count() >= 1) {
            $releasedDate = new \DateTime($itemNode->filterXPath("//default:pubDate")->first()->text());
        }
        return $releasedDate;
    }

}
