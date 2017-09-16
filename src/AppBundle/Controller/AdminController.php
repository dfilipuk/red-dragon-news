<?php

namespace AppBundle\Controller;


use AppBundle\Service\AjaxRequestManager;
use AppBundle\Service\NewsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
    public function usersAction(Request $request, AjaxRequestManager $ajaxRequestManager)
    {

    }
}