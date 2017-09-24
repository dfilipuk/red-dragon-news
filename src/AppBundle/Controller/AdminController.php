<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Category;
use AppBundle\Form\ArticleNewType;
use AppBundle\Form\CategoryEditType;
use AppBundle\Form\CategoryNewType;
use AppBundle\Form\UserEditType;
use AppBundle\Service\AjaxDataManager;
use AppBundle\Service\AjaxRequestManager;
use AppBundle\Service\CategoryManager;
use AppBundle\Service\NewsManager;
use AppBundle\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
      *@Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/users/{id}/edit", name="edit-user", requirements={"id": "\d{0,9}"})
     */
    public function editUserAction(int $id, UserManager $userManager, Request $request)
    {
        $user = $userManager->getUserById($id);
        if ($user === null){
            throw $this->createNotFoundException();
        }
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
     * @Route("/admin/users/{id}/delete", name="delete-user", requirements={"id": "\d{0,9}"})
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
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/categories", name="categories_page")
     */
    public function categoriesPageAction()
    {
        return $this->render('admin/categories.html.twig');
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/categories/{id}/edit", name="edit-category", requirements={"id": "\d{0,9}"})
     */
    public function editCategoryAction(int $id, CategoryManager $categoryManager, Request $request)
    {
        $category = $categoryManager->getCategoryById($id);
        if ($category === null){
            throw $this->createNotFoundException();
        }
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
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/categories/{id}/delete", name="delete-category", requirements={"id": "\d{0,9}"})
     */
    public function deleteCategoryAction(int $id, CategoryManager $categoryManager)
    {
        $categoryManager->deleteCategoryById($id);
        return $this->redirectToRoute('categories_page');
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/categories/create", name="create-category")
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
     * @Route("/admin/ajax/similar-categories/{level}", name="ajax_similar_categories")
     */
    public function similarCategoriesAction(Request $request, CategoryManager $categoryManager, int $level)
    {
        $similar = $request->request->get('similar');

        if ($similar !== null) {
            $categories = $categoryManager->getSimilarCategoriesForAjax($similar, $level);
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

    /**
     * @Route("/admin/articles", name="articles_page")
     */
    public function articlesPageAction()
    {
        return $this->render('admin/articles.html.twig');
    }

    /**
     * @Route("/admin/ajax/articles", name="ajax_articles")
     */
    public function articlesAction(Request $request, AjaxRequestManager $ajaxRequestManager, AjaxDataManager $dataManager)
    {
        if ($ajaxRequestManager->parseRequestParams($request)) {
            $result = [
                'success' => true,
                'items' => $dataManager->getArticlesList($ajaxRequestManager),
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
     * @Route("/admin/articles/create", name="create-article")
     */
    public function createArticleAction(Request $request, NewsManager $newsManager, CategoryManager $categoryManager)
    {

        $newArticle = new Article();
        $form = $this->createForm(ArticleNewType::class, $newArticle);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $similar = $request->request->get('similarNews');
            $similars = explode(",", $similar);
            if($similars[0] === ""){
                $similars = null;
            }
            $savePath = $this->getParameter('pictures_directory');
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $category = $categoryManager->getCategoryByName($newArticle->getCategory());
            $newsManager->createArticle($newArticle, $form, $user, $category, $savePath, $similars);
            return $this->render('admin/articles.html.twig');
        }
        return $this->render('admin/create_article.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true, true)
        ]);
    }

    /**
     * @Route("/admin/articles/{id}/edit", name="edit-article", requirements={"id": "\d{0,9}"})
     */
    public function editArticleAction(int $id, Request $request, NewsManager $newsManager, CategoryManager $categoryManager)
    {
        $article = $newsManager->findNewsById($id);
        if ($article === null){
            throw $this->createNotFoundException();
        }
        $similars = $article->getSimilarArticles();
        $article->setCategory($article->getCategory()->getName());
        $oldPicture = $article->getPicture();
        $article->setPicture(null);
        $form = $this->createForm(ArticleNewType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $similar = $request->request->get('similarNews');
            $similars = explode(",", $similar);
            if($similars[0] === ""){
                $similars = null;
            }
            $savePath = $this->getParameter('pictures_directory');
            $category = $categoryManager->getCategoryByName($article->getCategory());
            $newsManager->editArticle($article, $form, $category, $savePath, $oldPicture, $similars);
            return $this->render('admin/articles.html.twig');
        }
        return $this->render('admin/edit_article.html.twig', [
            'similars' => $similars,
            'article' => $article->getId(),
            'form' => $form->createView(),
            'errors' => $form->getErrors(true, true)
        ]);
    }

    /**
     * @Route("/admin/articles/{id}/delete", name="delete-article", requirements={"id": "\d{0,9}"})
     */
    public function deleteArticleAction(int $id, NewsManager $newsManager)
    {
        $savePath = $this->getParameter('pictures_directory');
        $newsManager->deleteArticleById($id, $savePath);
        return $this->render('admin/articles.html.twig');
    }


    /**
     * @Route("/admin/ajax/search", name="ajax_search")
     */
    public function seacrhAction(Request $request)
    {
        $finder = $this->container->get('fos_elastica.finder.search.posts');
        $searchRequest = urldecode($request->query->get('term'));
        $searchedNews = $finder->find('*'.$searchRequest . '*');
        $responseArray = [];
        foreach ($searchedNews as $searchedNewOne){
            array_push($responseArray, [$searchedNewOne->getId(), $searchedNewOne->getTitle()]);
        }
        $response = new Response(json_encode($responseArray));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}