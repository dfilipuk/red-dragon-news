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
use AppBundle\Repository\ArticleRepository;
use AppBundle\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class NewsManager
{
    private $newsRepository;
    private $categoryRepository;

    public function __construct(ArticleRepository $articleRepository, CategoryRepository $categoryRepository) {
        $this->newsRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function findAllNews() {
        return $this->newsRepository->findAll();
    }

    public function findAllGeneralCategories() {
        return $this->categoryRepository->findAllGeneralCategories();
    }


}