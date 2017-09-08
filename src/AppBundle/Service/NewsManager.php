<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 14:35
 */

namespace AppBundle\Service;

use AppBundle\Entity\Category;
use AppBundle\Repository\ArticleRepository;
use AppBundle\Repository\CategoryRepository;

class NewsManager
{

    private $newsRepository;
    private $categoryRepository;

    public function __construct(ArticleRepository $articleRepository, CategoryRepository $categoryRepository)
    {
        $this->newsRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
    }

    private function checkOnNull(Category $category)
    {
        $parent = $category->getParent();
        if ($parent == null){
            return 0;
        } else {
            return $parent->getId();
        }
    }

    private function sortByParentID(Category $a, Category $b)
    {
        $aParentID = $this->checkOnNull($a);
        $bParentID = $this->checkOnNull($b);
        if($aParentID == $bParentID){
            return 0;
        } else if($aParentID > $bParentID){
            return -1;
        } else {
            return 1;
        }
    }

    private function categoriesToArray(array $categories): array
    {
        $categoryArray = [];
        foreach ($categories as $category){
            $temp = [];
            $temp['id'] = $category->getId();
            $temp['name'] = $category->getName();
            if ($category->getParent() === null){
                $temp['parent_id'] = 0;
            } else {
                $temp['parent_id'] = $category->getParent()->getId();
            }
            array_push($categoryArray, $temp);
        }
        return $categoryArray;
    }

    private function makeTree(array $items, int $root = 0): array
    {
        $tree = [];
        foreach($items as $item) {
            if($item['parent_id'] == $root && $item['id'] != $item['parent_id']) {
                unset($items[$item['id']]);
                $tree[$item['id']] = array(
                    $item['id'] => $item,
                    'children' => $this->makeTree($items, $item['id'] )
                );
            }
        }
        return $tree;
    }

    private function transformToTree(array $categories): array
    {
        $transformedArray = $this->categoriesToArray($categories);
        return $this->makeTree($transformedArray);

    }

    public function getSortedCategories(): array
    {
        $categories = $this->findAllCategories();
        $categories = $this->transformToTree($categories);
        return $categories;
    }

    public function findAllNews()
    {
        return $this->newsRepository->findAll();
    }

    public function findAllGeneralCategories()
    {
        return $this->categoryRepository->findAllGeneralCategories();
    }

    public function findAllCategories()
    {
        return $this->categoryRepository->findAll();
    }
}