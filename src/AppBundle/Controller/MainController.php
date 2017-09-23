<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 13:19
 */

namespace AppBundle\Controller;


use AppBundle\Service\NewsManager;
use AppBundle\Service\SubscriptionManager;
use AppBundle\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncode;

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
        return $this->render("main/index.html.twig", ['news' => $newsOnPage, 'categories' => $generalCategories, 'title' => 'Red Dragon news']);
    }

    /**
     * @Route("/main/{category}", name="category")
     */
    public function showCategoryNewsAction(string $category, Request $request, NewsManager $newsManager)
    {
        $generalCategories = $newsManager->findGeneralCategories();
        if ($category === 'all-categories'){
            return $this->render("main/all_categories.html.twig", ['categories' => $generalCategories]);
        }
        $currentCategoryNews = $newsManager->findNewsByCategory($category);
        $newsOnPage = $this->paginateNews($request, $currentCategoryNews);
        return $this->render("main/index.html.twig", ['news' => $newsOnPage, 'categories' => $generalCategories, 'title' => $category]);
    }

    /**
     * @Route("/main/news/{id}", name="news-page", requirements={"id": "\d+"})
     */
    public function showNewsAction(int $id, Request $request, NewsManager $newsManager)
    {
        $generalCategories = $newsManager->findGeneralCategories();
        $oneNews = $newsManager->findNewsById($id);
        if ($oneNews === null)
            return $this->redirectToRoute("homepage");
        return $this->render("main/news.html.twig", ['news' => $oneNews, 'categories' => $generalCategories]);
    }

    /**
     * @Route("/load-tree", name="load-tree", methods="POST")
     */
    public function loadTreeAction(NewsManager $newsManager)
    {
        $response = new Response(json_encode($newsManager->getSortedCategories()));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/update-watch-count/{id}", name="update-watch-count", requirements={"id": "\d+"}, methods="POST")
     */
    public function updateWatchCountAction(NewsManager $newsManager, int $id)
    {
        $newsManager->updateWatchCount($id);
        return new Response();
    }

    /**
     * @Route("/subscribe-user", name="subscribe-user", methods={"POST"})
     */
    public function subscribeUserAction(Request $request, SubscriptionManager $subscriptionManager, UserManager $userManager)
    {
        $subscribe = $request->request->get('subscribe');
        if ($subscribe){
            $type = $request->request->get('type');
        } else{
            $type = null;
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userManager->updateSubscribe($subscribe, $user);
        $subscriptionManager->subscribeUser($user, $type);
        return new Response();
    }

}