<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 14:35
 */

namespace AppBundle\Service;

use AppBundle\Entity\Article;
use AppBundle\Entity\Category;
use \Doctrine\Common\Persistence\ManagerRegistry;

class NewsManager
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
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

    private function makeCategoriesTree(array $items, int $root = 0): array
    {
        $tree = [];
        foreach($items as $item) {;
            if($item['parent_id'] == $root && $item['id'] != $item['parent_id']) {
                unset($items[$item['id']]);
                $tree[$item['id']] = array(
                    $item['id'] => $item,
                    'children' => $this->makeCategoriesTree($items, $item['id'] )
                );
            }
        }
        return $tree;
    }

    private function transformToTree(array $categories): array
    {
        $transformedArray = $this->categoriesToArray($categories);
        return $this->makeCategoriesTree($transformedArray);

    }

    public function getSortedCategories(): array
    {
        $categories = $this->findAllCategories();
        $categories = $this->transformToTree($categories);
        return $categories;
    }

    public function findAllNews(): array
    {
        return $this->doctrine->getManager()->getRepository(Article::class)->findAll();
    }

    public function findAllCategories(): array
    {
        return $this->doctrine->getManager()->getRepository(Category::class)->findAll();
    }

    public function findGeneralCategories(): array
    {
        return $this->doctrine->getManager()->getRepository(Category::class)->findGeneralCategories();
    }
}