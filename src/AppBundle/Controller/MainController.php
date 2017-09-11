<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 13:19
 */

namespace AppBundle\Controller;


use AppBundle\Service\NewsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{

    private function paginateNews(Request $request, array $news)
    {
        $paginator  = $this->get('knp_paginator');
        return $paginator->paginate(
            $news,
            $request->query->getInt('page', 1),
            10
        );

    }

    /**
     * @Route("/main/", name="homepage")
     */
    public function indexAction(Request $request, NewsManager $newsManager)
    {
        $allNews = $newsManager->findAllNews();
        $generalCategories = $newsManager->findGeneralCategories();
        $newsOnPage = $this->paginateNews($request, $allNews);
        return $this->render("main/index.html.twig", array('news' => $newsOnPage, 'categories' => $generalCategories, 'news_count' => count($newsOnPage)));
    }

    /**
     * @Route("/main/{category}", name="category")
     */
    public function showCategoryNewsAction(string $category, Request $request, NewsManager $newsManager)
    {
        $currentCategoryNews = $newsManager->findNewsByCategory($category);
        $generalCategories = $newsManager->findGeneralCategories();
        if ($category === 'all-categories'){
            return $this->render("base_main.html.twig", array('categories' => $generalCategories));
        }
        $newsOnPage = $this->paginateNews($request, $currentCategoryNews);

        return $this->render("main/index.html.twig", array('news' => $newsOnPage, 'categories' => $generalCategories, 'news_count' => count($newsOnPage)));
    }

}