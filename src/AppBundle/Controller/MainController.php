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
    /**
     * @Route("/main/", name="homepage")
     */
    public function indexAction(Request $request, NewsManager $newsManager)
    {
        $allNews = $newsManager->findAllNews();
        $generalCategories = $newsManager->findGeneralCategories();
        $paginator  = $this->get('knp_paginator');
        $newsOnPage = $paginator->paginate(
            $allNews,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render("main/index.html.twig", array('news' => $newsOnPage, 'categories' => $generalCategories));
    }
}