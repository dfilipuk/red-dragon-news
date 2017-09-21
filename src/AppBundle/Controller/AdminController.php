<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Form\CategoryEditType;
use AppBundle\Form\CategoryNewType;
use AppBundle\Form\UserEditType;
use AppBundle\Service\AjaxDataManager;
use AppBundle\Service\AjaxRequestManager;
use AppBundle\Service\CategoryManager;
use AppBundle\Service\NewsManager;
use AppBundle\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
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
    public function editCategoryAction(int $id, CategoryManager $categoryManager, Request $request)
    {
        $category = $categoryManager->getCategoryById($id);
        $form = $this->createForm(CategoryEditType::class, $category, ['validation_groups' => 'editCategory']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categoryManager->editCategory($category);
            return $this->redirectToRoute('categories_page');
        } else {
            return $this->render('admin/edit_category.html.twig', [
                'category' => $category,
                'form' => $form->createView(),
                'errors' => $form->getErrors(true, true)
            ]);
        }
    }

    /**
     * @Route("/admin/categories/{id}/delete", name="delete-category", requirements={"id": "\d+"})
     */
    public function deleteCategoryAction(int $id, CategoryManager $categoryManager)
    {
        $categoryManager->deleteCategoryById($id);
        return $this->redirectToRoute('categories_page');
    }

    /**
     * @Route("/admin/categories/create", name="create-category", requirements={"id": "\d+"})
     */
    public function createCategoryAction(Request $request, CategoryManager $categoryManager)
    {
        $newCategory = new Category();
        $parentCategory = null;
        $form = $this->createForm(CategoryNewType::class, $newCategory);
        $form->handleRequest($request);
        if ($this->validateNewCategoryForm($categoryManager, $form, $newCategory, $parentCategory)) {
            $categoryManager->addCategory($newCategory, $parentCategory);
            return $this->redirectToRoute('categories_page');
        } else {
            return $this->render('admin/new_category.html.twig', [
                'form' => $form->createView(),
                'errors' => $form->getErrors(true, true)
            ]);
        }
    }

    /**
     * @Route("/admin/ajax/similar-categories", name="ajax_similar_categories")
     */
    public function similarCategoriesAction(Request $request, CategoryManager $categoryManager)
    {
        $similar = $request->request->get('similar');
        if ($similar !== null) {
            $categories = $categoryManager->getSimilarCategoriesForAjax($similar);
            return new JsonResponse($categories);
        } else {
            return new JsonResponse([]);
        }
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

    private function validateNewCategoryForm(CategoryManager $categoryManager, Form $form, Category $newCategory,
                                             ?Category &$parentCategory)
    {
        if (!($form->isSubmitted() && $form->isValid())) {
            return false;
        }
        if ($newCategory->getIsRootCategory()) {
            return true;
        }
        $parentCategory = $categoryManager->getCategoryByName($newCategory->getParentName());
        if ($parentCategory === null) {
            $form->addError(new FormError('No such parent directory'));
            return false;
        }
        if ($parentCategory->isLeafOfTree()) {
            $form->addError(new FormError('Specified parent category can\'t have children'));
            return false;
        } else {
            return true;
        }
    }
}