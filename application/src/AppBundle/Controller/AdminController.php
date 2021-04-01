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
use Symfony\Component\Translation\TranslatorInterface as Translator;
use Symfony\Component\HttpFoundation\Session\SessionInterface as Session;

class AdminController extends Controller
{

    /**
     * @Route("/admin/", name="admin-home")
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        return $this->render('admin/home.html.twig');
    }

    /**
     * @Route("/admin/users", name="users_page")
     *
     * @return Response
     */
    public function usersPageAction(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    /**
     *@Security("has_role('ROLE_ADMIN')")
    * @Route("/admin/users/{id}/edit", name="edit-user", requirements={"id": "\d{0,9}"})
    *
    * @param int $id
    * @param UserManager $userManager
    * @param Request $request
    * @return Response
    */
    public function editUserAction(int $id, UserManager $userManager, Request $request): Response
    {
        $user = $userManager->getUserById($id);
        if ($user === null) {
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
     *
     * @param int $id
     * @param UserManager $userManager
     * @param Session $session
     * @return Response
     */
    public function deleteUserAction(int $id, UserManager $userManager, Session $session): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $adminId = $user->getId();
        $userManager->deleteUserById($id);
        if ($adminId === $id) {
            $this->get('security.token_storage')->setToken(null);
            $session->invalidate(0);
        }
        return $this->redirectToRoute('users_page');
    }


    /**
     * @Route("/admin/ajax/users", name="ajax_users")
     *
     * @param Request $request
     * @param AjaxRequestManager $ajaxRequestManager
     * @param AjaxDataManager $dataManager
     * @return JsonResponse
     */
    public function usersAction(Request $request, AjaxRequestManager $ajaxRequestManager, AjaxDataManager $dataManager): JsonResponse
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
     *
     * @return Response
     */
    public function categoriesPageAction(): Response
    {
        return $this->render('admin/categories.html.twig');
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/categories/{id}/edit", name="edit-category", requirements={"id": "\d{0,9}"})
     *
     * @param int $id
     * @param CategoryManager $categoryManager
     * @param Request $request
     * @return Response
     */
    public function editCategoryAction(int $id, CategoryManager $categoryManager, Request $request):Response
    {
        $category = $categoryManager->getCategoryById($id);
        if ($category === null) {
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
     *
     * @param int $id
     * @param CategoryManager $categoryManager
     * @return Response
     */
    public function deleteCategoryAction(int $id, CategoryManager $categoryManager): Response
    {
        $categoryManager->deleteCategoryById($id);
        return $this->redirectToRoute('categories_page');
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/categories/create", name="create-category")
     *
     * @param Request $request
     * @param CategoryManager $categoryManager
     * @param Translator $translator
     * @return Response
     */
    public function createCategoryAction(Request $request, CategoryManager $categoryManager, Translator $translator): Response
    {
        $newCategory = new Category();
        $parentCategory = null;
        $form = $this->createForm(CategoryNewType::class, $newCategory);
        $form->handleRequest($request);
        if ($this->validateNewCategoryForm($categoryManager, $form, $newCategory, $parentCategory, $translator)) {
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
     *
     * @param Request $request
     * @param CategoryManager $categoryManager
     * @param int $level
     * @return Response
     */
    public function similarCategoriesAction(Request $request, CategoryManager $categoryManager, int $level): Response
    {
        $similar = $request->request->get('similar');
        $categories = [];
        if ($similar !== null) {
            $categories = $categoryManager->getSimilarCategoriesForAjax($similar, $level);
        }

        $response = new Response(json_encode($categories));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/admin/ajax/categories", name="ajax_categories")
     *
     * @param Request $request
     * @param AjaxRequestManager $ajaxRequestManager
     * @param AjaxDataManager $dataManager
     * @return JsonResponse
     */
    public function categoriesAction(Request $request, AjaxRequestManager $ajaxRequestManager, AjaxDataManager $dataManager): JsonResponse
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

    /**
     * @param CategoryManager $categoryManager
     * @param Form $form
     * @param Category $newCategory
     * @param Category|null $parentCategory
     * @param Translator $translator
     * @return bool
     */
    private function validateNewCategoryForm(
        CategoryManager $categoryManager,
        Form $form,
        Category $newCategory,
                                             ?Category &$parentCategory,
        Translator $translator
    ): bool {
        if (!($form->isSubmitted() && $form->isValid())) {
            return false;
        }
        if ($newCategory->getIsRootCategory()) {
            return true;
        }
        $parentCategory = $categoryManager->getCategoryByName($newCategory->getParentName());
        if ($parentCategory === null) {
            $error = $translator->trans('admin.controller.error.validation.1');
            $form->addError(new FormError($error));
            return false;
        }
        if ($parentCategory->isLeafOfTree()) {
            $error = $translator->trans('admin.controller.error.validation.2');
            $form->addError(new FormError($error));
            return false;
        } else {
            return true;
        }
    }

    /**
     * @Route("/admin/articles", name="articles_page")
     *
     * @return Response
     */
    public function articlesPageAction(): Response
    {
        return $this->render('admin/articles.html.twig');
    }

    /**
     * @Route("/admin/ajax/articles", name="ajax_articles")
     *
     * @param Request $request
     * @param AjaxRequestManager $ajaxRequestManager
     * @param AjaxDataManager $dataManager
     * @return JsonResponse
     */
    public function articlesAction(Request $request, AjaxRequestManager $ajaxRequestManager, AjaxDataManager $dataManager): JsonResponse
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
     *
     * @param Request $request
     * @param NewsManager $newsManager
     * @param CategoryManager $categoryManager
     * @param Translator $translator
     * @return Response
     */
    public function createArticleAction(Request $request, NewsManager $newsManager, CategoryManager $categoryManager, Translator $translator): Response
    {
        $newArticle = new Article();
        $form = $this->createForm(ArticleNewType::class, $newArticle);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $similar = $request->request->get('similarNews');
            $similars = explode(",", $similar);
            if ($similars[0] === "") {
                $similars = null;
            }
            $savePath = $this->getParameter('pictures_directory');
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $category = $categoryManager->getCategoryByName($newArticle->getCategory());
            if ($category === null) {
                $error = $translator->trans('admin.controller.error.validation.3');
                $form->addError(new FormError($error));
            } else {
                $newsManager->createArticle($newArticle, $form, $user, $category, $savePath, $similars);
                return $this->render('admin/articles.html.twig');
            }
        }
        return $this->render('admin/create_article.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true, true)
        ]);
    }

    /**
     * @Route("/admin/articles/{id}/edit", name="edit-article", requirements={"id": "\d{0,9}"})
     *
     * @param int $id
     * @param Request $request
     * @param NewsManager $newsManager
     * @param CategoryManager $categoryManager
     * @return Response
     */
    public function editArticleAction(int $id, Request $request, NewsManager $newsManager, CategoryManager $categoryManager): Response
    {
        $article = $newsManager->findNewsById($id);
        if ($article === null) {
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
            if ($similars[0] === "") {
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
     *
     * @param int $id
     * @param NewsManager $newsManager
     * @return Response
     */
    public function deleteArticleAction(int $id, NewsManager $newsManager): Response
    {
        $savePath = $this->getParameter('pictures_directory');
        $newsManager->deleteArticleById($id, $savePath);
        return $this->render('admin/articles.html.twig');
    }


    /**
     * @Route("/admin/ajax/search", name="ajax_search")
     *
     * @param Request $request
     * @return Response
     */
    public function seacrhAction(Request $request): Response
    {
        $finder = $this->container->get('fos_elastica.finder.search.posts');
        $searchRequest = urldecode($request->query->get('term'));
        $searchedNews = $finder->find('*'.$searchRequest . '*');
        $responseArray = [];
        foreach ($searchedNews as $searchedNewOne) {
            array_push($responseArray, [$searchedNewOne->getId(), $searchedNewOne->getTitle()]);
        }
        $response = new Response(json_encode($responseArray));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
