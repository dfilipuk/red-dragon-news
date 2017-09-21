<?php

namespace AppBundle\Service;

use AppBundle\Entity\Category;
use \Doctrine\Common\Persistence\ManagerRegistry;

class CategoryManager
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getCategoryById(int $id):? Category
    {
        $repository = $this->doctrine->getManager()->getRepository(Category::class);
        return $repository->findOneBy(['id' => $id]);
    }

    public function getCategoryByName(string $name):? Category
    {
        $repository = $this->doctrine->getManager()->getRepository(Category::class);
        return $repository->findOneBy(['name' => $name]);
    }

    public function editCategory(Category $category)
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($category);
        $manager->flush();
    }

    public function deleteCategoryById(int $id)
    {
        $manager = $this->doctrine->getManager();
        $category = $this->getCategoryById($id);
        if ($category !== null) {
            $manager->remove($category);
            $manager->flush();
        }
    }

    public function addCategory(Category $newCategory, ?Category $parentCategory)
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
}