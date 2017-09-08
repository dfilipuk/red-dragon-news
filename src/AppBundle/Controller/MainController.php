<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 13:19
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Article;
use AppBundle\Entity\Category;
use AppBundle\Service\NewsManager;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    /**
     * @Route("/main/", name="home")
     */
    public function indexAction(Request $request)
    {
        $newsManager = new NewsManager($this->getDoctrine()->getRepository(Article::class), $this->getDoctrine()->getRepository(Category::class));
        $allNews = $newsManager->findAllNews();
        $generalCategories = $newsManager->findAllGeneralCategories();
        //$generalCategories = $newsManager->getSortedCategories();
        $paginator  = $this->get('knp_paginator');
        $newsOnPage = $paginator->paginate(
            $allNews,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render("main/index.html.twig", array('news' => $newsOnPage, 'categories' => $generalCategories));
    }
}