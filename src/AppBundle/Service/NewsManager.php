<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 14:35
 */

namespace AppBundle\Service;


use AppBundle\Entity\Article;
use AppBundle\Repository\ArticleRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class NewsManager
{
    private $newsRepository;
    private $em;

    public function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
        $this->newsRepository = $this->em->getRepository(Article::class);
    }

    public function findAll() {
        return $this->newsRepository->findAll();
    }

}