<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 13:19
 */

namespace AppBundle\Controller;

use AppBundle\Service\NewsManager;
use AppBundle\Service\SessionManager;
use AppBundle\Service\SubscriptionManager;
use AppBundle\Service\UserManager;
use Elastica\Query;
use Elastica\Query\QueryString;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface as Translator;
use \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination as Paginator;

class MainController extends Controller
{
    /**
     * @param Request $request
     * @param $news
     * @return Paginator
     */
    private function paginateNews(Request $request, $news): Paginator
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
     *
     * @param Request $request
     * @param NewsManager $newsManager
     * @param SessionManager $sessionManager
     * @param Translator $translator
     * @return Response
     */
    public function indexAction(Request $request, NewsManager $newsManager, SessionManager $sessionManager, Translator $translator): Response
    {
        $isAscending = $sessionManager->getIsAscending();
        $isOrderByDate = $sessionManager->getIsOrderByDate();
        $allNews = $newsManager->findAllNews($isOrderByDate, $isAscending);
        $generalCategories = $newsManager->findGeneralCategories();
        $newsOnPage = $this->paginateNews($request, $allNews);
        $title = $translator->trans('main.base.title');
        return $this->render("main/index.html.twig", [
            'news' => $newsOnPage,
            'categories' => $generalCategories,
            'title' => $title,
            'isAscending' => $isAscending,
            'isOrderByDate' => $isOrderByDate
        ]);
    }

    /**
     * @Route("/main/{category}", name="category")
     *
     * @param string $category
     * @param Request $request
     * @param NewsManager $newsManager
     * @param SessionManager $sessionManager
     * @return Response
     */
    public function showCategoryNewsAction(string $category, Request $request, NewsManager $newsManager, SessionManager $sessionManager): Response
    {
        $isAscending = $sessionManager->getIsAscending();
        $isOrderByDate = $sessionManager->getIsOrderByDate();
        $generalCategories = $newsManager->findGeneralCategories();
        if ($category === 'all-categories'){
            return $this->render("main/all_categories.html.twig", [
                'categories' => $generalCategories,
                'isAscending' => $isAscending,
                'isOrderByDate' => $isOrderByDate
            ]);
        }
        $currentCategoryNews = $newsManager->findNewsByCategory($category, $sessionManager->getIsOrderByDate(), $sessionManager->getIsAscending());
        $newsOnPage = $this->paginateNews($request, $currentCategoryNews);
        return $this->render("main/index.html.twig", [
            'news' => $newsOnPage,
            'categories' => $generalCategories,
            'title' => $category,
            'isAscending' => $isAscending,
            'isOrderByDate' => $isOrderByDate
        ]);
    }

    /**
     * @Route("/main/news/{id}", name="news-page", requirements={"id": "\d+"})
     *
     * @param int $id
     * @param NewsManager $newsManager
     * @param SessionManager $sessionManager
     * @return Response
     */
    public function showNewsAction(int $id, NewsManager $newsManager, SessionManager $sessionManager): Response
    {
        $isAscending = $sessionManager->getIsAscending();
        $isOrderByDate = $sessionManager->getIsOrderByDate();
        $generalCategories = $newsManager->findGeneralCategories();
        $oneNews = $newsManager->findNewsById($id);
        $author = $oneNews->getAuthor();
        if ($author !== null){
            $author = $author->getEmail();
        } else{
            $author = "Anonymous";
        }
        if ($oneNews === null)
            return $this->redirectToRoute("homepage");
        return $this->render("main/news.html.twig", [
            'news' => $oneNews,
            'author' => $author,
            'categories' => $generalCategories,
            'isAscending' => $isAscending,
            'isOrderByDate' => $isOrderByDate
        ]);
    }

    /**
     * @Route("/load-tree", name="load-tree", methods="POST")
     *
     * @param NewsManager $newsManager
     * @return Response
     */
    public function loadTreeAction(NewsManager $newsManager): Response
    {
        $response = new Response(json_encode($newsManager->getSortedCategories()));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/update-watch-count/{id}", name="update-watch-count", requirements={"id": "\d+"}, methods="POST")
     *
     * @param NewsManager $newsManager
     * @param int $id
     * @return Response
     */
    public function updateWatchCountAction(NewsManager $newsManager, int $id): Response
    {
        $newsManager->updateWatchCount($id);
        return new Response();
    }

    /**
     * @Route("/subscribe-user", name="subscribe-user", methods={"POST"})
     *
     * @param Request $request
     * @param SubscriptionManager $subscriptionManager
     * @param UserManager $userManager
     * @return Response
     */
    public function subscribeUserAction(Request $request, SubscriptionManager $subscriptionManager, UserManager $userManager): Response
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

    /**
     * @Route("/search", name="search")
     *
     * @param Request $request
     * @param NewsManager $newsManager
     * @param SessionManager $sessionManager
     * @param Translator $translator
     * @return Response
     */
    public function searchAction(Request $request, NewsManager $newsManager, SessionManager $sessionManager, Translator $translator): Response
    {
        $isAscending = $sessionManager->getIsAscending();
        $isOrderByDate = $sessionManager->getIsOrderByDate();
        $finder = $this->container->get('fos_elastica.finder.search.posts');
        $searchRequest = $request->query->get('search');

        $keywordQuery = new QueryString();
        $keywordQuery->setQuery('*'.$searchRequest . '*');
        $query = new Query();
        $query->setQuery($keywordQuery);
        $query->setSort([
            ($isOrderByDate ? 'date' : 'viewsCount') => ($isAscending ? 'asc' : 'desc')
        ]);
        
        $searchedNews = $finder->createPaginatorAdapter($query);

        $generalCategories = $newsManager->findGeneralCategories();
        $newsOnPage = $this->paginateNews($request, $searchedNews);
        $title = $translator->trans('main.base.title');
        return $this->render("main/index.html.twig", [
            'news' => $newsOnPage,
            'categories' => $generalCategories,
            'title' => $title,
            'isAscending' => $isAscending,
            'isOrderByDate' => $isOrderByDate
        ]);
    }

    /**
     * @Route("/sorting-params/{isAscending}/{isOrderByDate}",
     *     name="sorting-params",
     *     requirements={"isAscending": "0|1", "isOrderByDate": "0|1"})
     *
     * @param int $isAscending
     * @param int $isOrderByDate
     * @param SessionManager $sessionManager
     * @return Response
     */
    public function setSortingParams(int $isAscending, int $isOrderByDate, SessionManager $sessionManager): Response
    {
        $sessionManager->setIsAscending($isAscending == 1);
        $sessionManager->setIsOrderByDate($isOrderByDate == 1);
        return $this->redirectToRoute('homepage');
    }
}