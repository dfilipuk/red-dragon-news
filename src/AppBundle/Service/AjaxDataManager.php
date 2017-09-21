<?php

namespace AppBundle\Service;

use AppBundle\Entity\Article;
use AppBundle\Entity\Category;
use DateTime;
use \Doctrine\Common\Persistence\ManagerRegistry;
use AppBundle\Entity\User;

class AjaxDataManager
{
    private const ACTIVE_USER = 'active';
    private const DISABLED_USER = 'disabled';
    private const MAGIC_CONST = 2;

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getUsersList(AjaxRequestManager $ajaxRequestManager): array
    {
        $repository = $this->doctrine->getManager()->getRepository(User::class);
        $itemsAmount = $repository->getUsersCount(
            $ajaxRequestManager->getSortColumn(),
            $ajaxRequestManager->isAscendingSort(),
            $this->prepareFiltersForUserEntity($ajaxRequestManager->getFilters())
        );
        $ajaxRequestManager->setPagesAmo($this->getPagesAmount($ajaxRequestManager, $itemsAmount));
        $offset = $this->getPageOffset($ajaxRequestManager);
        $users = $repository->getUsersList(
            $ajaxRequestManager->getSortColumn(),
            $ajaxRequestManager->isAscendingSort(),
            $this->prepareFiltersForUserEntity($ajaxRequestManager->getFilters()),
            $offset,
            $ajaxRequestManager->getRowsPerPage()
        );
        return $this->convertUserObjectsToArray($users);
    }

    public function getCategoriesList(AjaxRequestManager $ajaxRequestManager): array
    {
        $repository = $this->doctrine->getManager()->getRepository(Category::class);
        $itemsAmount = $repository->getCategoriesCount(
            $ajaxRequestManager->getSortColumn(),
            $ajaxRequestManager->isAscendingSort(),
            $ajaxRequestManager->getFilters()
        );
        $ajaxRequestManager->setPagesAmo($this->getPagesAmount($ajaxRequestManager, $itemsAmount));
        $offset = $this->getPageOffset($ajaxRequestManager);
        $categories = $repository->getCategoriesList(
            $ajaxRequestManager->getSortColumn(),
            $ajaxRequestManager->isAscendingSort(),
            $ajaxRequestManager->getFilters(),
            $offset,
            $ajaxRequestManager->getRowsPerPage()
        );
        return $this->convertCategoriesObjectsToArray($categories);
    }

    public function getArticlesList(AjaxRequestManager $ajaxRequestManager): array
    {
        $repository = $this->doctrine->getManager()->getRepository(Article::class);
        $filters = $this->prepareFiltersForArticleEntity($ajaxRequestManager->getFilters());
        $sortedColumn = $this->prepareArticleSortColumn($ajaxRequestManager->getSortColumn());
        $itemsAmount = $repository->getArticlesCount(
            $sortedColumn,
            $ajaxRequestManager->isAscendingSort(),
            $filters
        );
        $ajaxRequestManager->setPagesAmo($this->getPagesAmount($ajaxRequestManager, $itemsAmount));
        $offset = $this->getPageOffset($ajaxRequestManager);
        $articles = $repository->getArticlesList(
            $sortedColumn,
            $ajaxRequestManager->isAscendingSort(),
            $filters,
            $offset,
            $ajaxRequestManager->getRowsPerPage()
        );
        return $this->convertArticlesObjectsToArray($articles);
    }

    private function prepareFiltersForUserEntity(array $filters): array
    {
        $result = [];
        $key = array_search('email', array_column($filters, 0));
        if ($key !== false) {
            $result[] = [
                0 => 'email',
                1 => $filters[$key][1]
            ];
        }
        $key = array_search('role', array_column($filters, 0));
        if ($key !== false) {
            $result[] = [
                0 => 'role',
                1 => 'ROLE_' . strtoupper($filters[$key][1])
            ];
        }
        $key = array_search('isActive', array_column($filters, 0));
        if ($key !== false) {
            $isActive = strtolower($filters[$key][1]);
            if ($isActive === self::ACTIVE_USER) {
                $result[] = [
                    0 => 'isActive',
                    1 => true
                ];
            } elseif ($isActive === self::DISABLED_USER) {
                $result[] = [
                    0 => 'isActive',
                    1 => false
                ];
            } else {
                //shit code
                $result[] = [
                    0 => 'isActive',
                    1 => self::MAGIC_CONST
                ];

            }
        }
        return $result;
    }

    private function prepareFiltersForArticleEntity(array $filters): array
    {
        $result = [];
        $key = array_search('author', array_column($filters, 0));
        if ($key !== false) {
            $result[] = [
                0 => 'author.email',
                1 => '%'.$filters[$key][1].'%'
            ];
        }

        $key = array_search('category', array_column($filters, 0));
        if ($key !== false) {
            $result[] = [
                0 => 'category.name',
                1 => '%'.$filters[$key][1].'%'
            ];
        }

        $key = array_search('title', array_column($filters, 0));
        if ($key !== false) {
            $result[] = [
                0 => 'a.title',
                1 => '%'.$filters[$key][1].'%'
            ];
        }

        $key = array_search('date', array_column($filters, 0));
        if ($key !== false) {
            $date = DateTime::createFromFormat('d-m-Y', $filters[$key][1]);
            $date = $date->format('Y-m-d');
            $result[] = [
                0 => 'a.date',
                1 => $date
            ];
        }

        $key = array_search('viewsCount', array_column($filters, 0));
        if ($key !== false) {
            $result[] = [
                0 => 'a.viewsCount',
                1 => $filters[$key][1]
            ];
        }

        return $result;
    }

    private function prepareArticleSortColumn(string $sortColumn): string
    {
        $result = 'a.'.$sortColumn;
        if ($sortColumn == 'author')
            $result = 'author.email';
        if ($sortColumn == 'category')
            $result = 'category.name';
        return $result;
    }

    private function convertUserObjectsToArray(array $users): array
    {
        $result = [];
        for ($i = 0; $i < count($users); $i++) {
            if ($users[$i]->getIsActive())
            {
                $isActive = self::ACTIVE_USER;
            } else{
                $isActive = self::DISABLED_USER;
            }
            $result[$i] = [
                $users[$i]->getId(),
                $users[$i]->getUsername(),
                strtolower(substr($users[$i]->getRole(), 5)),
                $isActive
            ];
        }
        return $result;
    }

    private function convertArticlesObjectsToArray(array $articles): array
    {
        $result = [];
        $sliceCount = 100;
        for ($i = 0; $i < count($articles); $i++) {
            $title = $articles[$i]->getTitle();
            if (strlen($title) < $sliceCount){
                $title = substr($title, 0, strlen($title));
            } else{
                $title = substr($title, 0, $sliceCount);
                $title = rtrim($title, "!,.-");
                $title = substr($title, 0, strrpos($title, ' '));
                $title .= '...';
            }
            $result[$i] = [
                $articles[$i]->getId(),
                $articles[$i]->getAuthor()->getEmail(),
                $articles[$i]->getCategory()->getName(),
                $title,
                $articles[$i]->getDate()->format('d-m-Y'),
                $articles[$i]->getViewsCount(),
            ];
        }
        return $result;
    }

    private function convertCategoriesObjectsToArray(array $catefories): array
    {
        $result = [];
        for ($i = 0; $i < count($catefories); $i++) {
            $result[$i] = [
                $catefories[$i]->getId(),
                $catefories[$i]->getName(),
            ];
        }
        return $result;
    }

    private function getPageOffset(AjaxRequestManager $ajaxRequestManager): int
    {
        return ($ajaxRequestManager->getPage() - 1) * $ajaxRequestManager->getRowsPerPage();
    }

    private function getPagesAmount(AjaxRequestManager $ajaxRequestManager, int $itemsAmount): int
    {
        if (($itemsAmount % $ajaxRequestManager->getRowsPerPage()) === 0) {
            return intdiv($itemsAmount, $ajaxRequestManager->getRowsPerPage());
        } else {
            return intdiv($itemsAmount, $ajaxRequestManager->getRowsPerPage()) + 1;
        }
    }
}