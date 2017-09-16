<?php

namespace AppBundle\Controller;


use AppBundle\Service\AjaxRequestManager;
use AppBundle\Service\NewsManager;
use AppBundle\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Route("/admin/users", name="users_page")
     */
    public function usersPageAction(NewsManager $newsManager)
    {
        $categories = $newsManager->findGeneralCategories();
        return $this->render('admin/users.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/admin/ajax/users", name="ajax_users")
     */
    public function usersAction(Request $request, AjaxRequestManager $ajaxRequestManager, UserManager $userManager)
    {
        if ($ajaxRequestManager->parseRequestParams($request)) {
            $result = [
                'success' => true,
                'items' => $userManager->getSpecifiedUsersList($ajaxRequestManager),
                'pagesAmo' => $ajaxRequestManager->getPagesAmo()
            ];
        } else {
            $result = [
                'success' => false
            ];
        }
        return new JsonResponse($result);
    }
}