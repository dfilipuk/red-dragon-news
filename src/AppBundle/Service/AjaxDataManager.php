<?php

namespace AppBundle\Service;

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