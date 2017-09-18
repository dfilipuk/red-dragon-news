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
                    $ajaxRequestManager->getFilters()
                );
                break;
            default:
                $itemsAmount = $repository->getAllUserCount();
                $ajaxRequestManager->setPagesAmo($this->getPagesAmount($ajaxRequestManager, $itemsAmount));

                $offset = $this->getPageOffset($ajaxRequestManager);
                $users = $repository->getAllUsers($offset, $ajaxRequestManager->getRowsPerPage());
        }
        return $this->convertUserObjectsToArray($users);
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