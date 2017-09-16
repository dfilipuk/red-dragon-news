<?php

namespace AppBundle\Service;

use \Doctrine\Common\Persistence\ManagerRegistry;
use AppBundle\Entity\User;

class AjaxDataManager
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getUsersList(AjaxRequestManager $ajaxRequestManager): array
    {
        $repository = $this->doctrine->getManager()->getRepository(User::class);
        switch ($ajaxRequestManager->getQueryType()) {
            case AjaxRequestManager::PAGINATION_SORT:
                $users = $repository->getSortedUsers(
                    $ajaxRequestManager->getSortColumn(),
                    $ajaxRequestManager->isAscendingSort()
                );
                break;
            case AjaxRequestManager::PAGINATION_FILTER:
                $users = $repository->getFilteredUsers(
                    $ajaxRequestManager->getFilterColumn(),
                    $ajaxRequestManager->getFilterPattern()
                );
                break;
            case AjaxRequestManager::PAGINATION_SORT_FILTER:
                $users = $repository->getSortedAndFilteredUsers(
                    $ajaxRequestManager->getSortColumn(),
                    $ajaxRequestManager->isAscendingSort(),
                    $ajaxRequestManager->getFilterColumn(),
                    $ajaxRequestManager->getFilterPattern()
                );
                break;
            default:
                $users = $repository->getAllUsers();
        }
        $page = $this->paginateList($ajaxRequestManager, $users);
        return $this->convertUserObjectsToArray($page);
    }

    private function convertUserObjectsToArray(array $users): array
    {
        $result = [];
        for ($i = 0; $i < count($users); $i++) {
            $result[$i] = [
                $users[$i]->getUsername(),
                $users[$i]->getRole(),
                $users[$i]->getIsActive()
            ];
        }
        return $result;
    }

    private function paginateList(AjaxRequestManager $ajaxRequestManager, array $list): array
    {
        $itemsAmount = count($list);
        if ($itemsAmount > 0) {
            if (($itemsAmount % $ajaxRequestManager->getRowsPerPage()) === 0) {
                $pagesAmo = intdiv($itemsAmount, $ajaxRequestManager->getRowsPerPage());
            } else {
                $pagesAmo = intdiv($itemsAmount, $ajaxRequestManager->getRowsPerPage()) + 1;
            }
            $ajaxRequestManager->setPagesAmo($pagesAmo);
            $offset = ($ajaxRequestManager->getPage() - 1) * $ajaxRequestManager->getRowsPerPage();
            return array_slice($list, $offset, $ajaxRequestManager->getRowsPerPage());
        } else {
            return [];
        }
    }


}