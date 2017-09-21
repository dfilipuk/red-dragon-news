<?php

namespace AppBundle\Controller;


use AppBundle\Form\UserEditType;
use AppBundle\Service\AjaxDataManager;
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
     * @Route("/admin/", name="admin-home")
     */
    public function indexAction()
    {
        return $this->render('admin/home.html.twig');
    }

    /**
     * @Route("/admin/users", name="users_page")
     */
    public function usersPageAction()
    {
        return $this->render('admin/users.html.twig');
    }

     /**
     * @Route("/admin/users/{id}/edit", name="edit-user", requirements={"id": "\d+"})
     */
    public function editUserAction(int $id, UserManager $userManager, Request $request)
    {
        $user = $userManager->getUserById($id);
        $originalPassword = $user->getPassword();
        $form = $this->createForm(UserEditType::class, $user, ['validation_groups' => 'editUser']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $userManager->editUser($user, $form, $originalPassword);
            return $this->render('admin/users.html.twig');
        }
        return $this->render('admin/edit_user.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'errors' => $form->getErrors(true, true)
        ]);
    }

    /**
     * @Route("/admin/users/{id}/delete", name="delete-user", requirements={"id": "\d+"})
     */
    public function deleteUserAction(int $id, UserManager $userManager)
    {
        $userManager->deleteUserById($id);
        return $this->render('admin/users.html.twig');
    }


    /**
     * @Route("/admin/ajax/users", name="ajax_users")
     */
    public function usersAction(Request $request, AjaxRequestManager $ajaxRequestManager, AjaxDataManager $dataManager)
    {
        if ($ajaxRequestManager->parseRequestParams($request)) {
            $result = [
                'success' => true,
                'items' => $dataManager->getUsersList($ajaxRequestManager),
                'pagesAmo' => $ajaxRequestManager->getPagesAmo()
            ];
        } else {
            $result = [
                'success' => false
            ];
        }
        return new JsonResponse($result);
    }

    /**
     * @Route("/admin/categories", name="categories_page")
     */
    public function categoriesPageAction()
    {
        return $this->render('admin/categories.html.twig');
    }

    /**
     * @Route("/admin/categories/{id}/edit", name="edit-category", requirements={"id": "\d+"})
     */
    public function editCategoryAction(int $id, UserManager $userManager, Request $request)
    {
        return $this->redirectToRoute('admin-home');
    }

    /**
     * @Route("/admin/ajax/categories", name="ajax_categories")
     */
    public function categoriesAction(Request $request, AjaxRequestManager $ajaxRequestManager, AjaxDataManager $dataManager)
    {
        if ($ajaxRequestManager->parseRequestParams($request)) {
            $result = [
                'success' => true,
                'items' => $dataManager->getCategoriesList($ajaxRequestManager),
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