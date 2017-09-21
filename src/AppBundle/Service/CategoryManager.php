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
}