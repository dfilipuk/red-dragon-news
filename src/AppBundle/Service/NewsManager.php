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
    private const TREE_ROOT = 0;
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
                $temp['parent_id'] = self::TREE_ROOT;
            } else {
                $temp['parent_id'] = $category->getParent()->getId();
            }
            array_push($categoryArray, $temp);
        }
        return $categoryArray;
    }

    private function makeCategoriesTree(array $items, int $root = self::TREE_ROOT): array
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

    private function getCategoriesAndChildrensID(array $categoryAndChildrensTree, array &$categoriesID): void
    {
        if(!is_null($categoryAndChildrensTree) && count($categoryAndChildrensTree) > 0) {
            foreach($categoryAndChildrensTree as $key=>$node) {
                array_push($categoriesID,$node[$key]['id']);
                $this->getCategoriesAndChildrensID($node['children'], $categoriesID);
            }
        }
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

    public function getCategoryAndChildrenID(string $category): ?array
    {
        $categories = $this->findAllCategories();
        $transformedArray = $this->categoriesToArray($categories);
        $category = array_search($category, array_column($transformedArray, 'name'));
        $categoryWithChildrensID = [];
        if ($category === false){
            return $categoryWithChildrensID;
        }
        $categoryID = $transformedArray[$category]['id'];
        $categoryAndChildrensTree = $this->makeCategoriesTree($transformedArray, $categoryID);
        array_push($categoryWithChildrensID, $categoryID);
        $this->getCategoriesAndChildrensID($categoryAndChildrensTree, $categoryWithChildrensID);
        return $categoryWithChildrensID;
    }

    public function findNewsByCategory(string $category): ?array
    {
        $categoriesID = $this->getCategoryAndChildrenID($category);
        $result = [];
        if (array_key_exists(0, $categoriesID)){
            $result = $this->doctrine->getManager()->getRepository(Article::class)->findNewsByCategory($categoriesID);
        }
        return $result;
    }

    public function findNewsById(int $id)
    {
        $result = $this->doctrine->getManager()->getRepository(Article::class)->findNewsById($id);
        return $result[0];
    }
}