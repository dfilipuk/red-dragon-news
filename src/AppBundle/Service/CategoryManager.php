<?php

namespace AppBundle\Service;

use AppBundle\Entity\Category;
use \Doctrine\Common\Persistence\ManagerRegistry;

class CategoryManager
{
    private $doctrine;

    /**
     * CategoryManager constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param int $id
     * @return Category|null
     */
    public function getCategoryById(int $id): ?Category
    {
        $result = $this->doctrine->getManager()->getRepository(Category::class)->findCategoryById($id);
        return array_key_exists(0, $result) ? $result[0] : null;
    }

    /**
     * @param string $name
     * @return Category|null
     */
    public function getCategoryByName(string $name): ?Category
    {
        $repository = $this->doctrine->getManager()->getRepository(Category::class);
        return $repository->findOneBy(['name' => $name]);
    }

    /**
     * @param Category $category
     */
    public function editCategory(Category $category): void
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($category);
        $manager->flush();
    }

    /**
     * @param int $id
     */
    public function deleteCategoryById(int $id): void
    {
        $manager = $this->doctrine->getManager();
        $category = $this->getCategoryById($id);
        if (($category !== null) && ($category->isPossibleToDelete())) {
            $manager->remove($category);
            $manager->flush();
        }
    }

    /**
     * @param Category $newCategory
     * @param Category|null $parentCategory
     */
    public function addCategory(Category $newCategory, ?Category $parentCategory): void
    {
        $repository = $this->doctrine->getManager();
        if ($parentCategory !== null) {
            $newCategory->setLevel($parentCategory->getLevel() + 1);
            $newCategory->setParent($parentCategory);
        } else {
            $newCategory->setLevel(0);
        }
        $repository->persist($newCategory);
        $repository->flush();
    }

    /**
     * @param string $similar
     * @param int $maxLevel
     * @return array
     */
    public function getSimilarCategoriesForAjax(string $similar, int $maxLevel): array
    {
        $repository = $this->doctrine->getRepository(Category::class);
        $categories = $repository->getSimilarCategories($similar, $maxLevel);
        return $this->converteCategoryiesEntitiesToArray($categories);
    }

    /**
     * @param array $categories
     * @return array
     */
    private function converteCategoryiesEntitiesToArray(array $categories): array
    {
        $result = [];
        for ($i = 0; $i < count($categories); $i++) {
            $result[] = $categories[$i]->getName();
        }
        return $result;
    }
}