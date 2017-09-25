<?php

namespace AppBundle\Service;


use Symfony\Component\HttpFoundation\Request;

class AjaxRequestManager
{
    private $page;
    private $rowsPerPage;
    private $isSorted;
    private $isAscendingSort;
    private $sortColumn;
    private $isFiltered;
    private $filters;
    private $pagesAmo;

    /**
     * AjaxRequestManager constructor.
     */
    public function __construct()
    {
        $this->page = 0;
        $this->rowsPerPage = 0;
        $this->isSorted = false;
        $this->isAscendingSort = false;
        $this->sortColumn = null;
        $this->isFiltered = false;
        $this->pagesAmo = 0;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function parseRequestParams(Request $request): bool
    {
        if (!$request->request->has('rowsamo')) {
            return false;
        }
        $this->rowsPerPage = $request->request->get('rowsamo');
        if ($request->request->has('page')) {
            $this->page = $request->request->get('page');
        } else {
            $this->page = 1;
        }
        if ($request->request->has('sortbyfield') && $request->request->has('order')) {
            $this->isSorted = true;
            $this->sortColumn = $request->request->get('sortbyfield');
            if ($request->request->get('order') === 'asc') {
                $this->isAscendingSort = true;
            } else {
                $this->isAscendingSort = false;
            }
        }
        $filters = $request->request->get('filters');
        if (count($filters) > 0) {
            $this->isFiltered = true;
            $this->filters = $filters;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        if ($this->filters === null) {
           return [];
        }
        return $this->filters;
    }

    /**
     * @return bool
     */
    public function isAscendingSort(): bool
    {
        return $this->isAscendingSort;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }

    /**
     * @return string
     */
    public function getSortColumn(): string
    {
        return $this->sortColumn;
    }

    /**
     * @return int
     */
    public function getPagesAmo(): int
    {
        return $this->pagesAmo;
    }

    /**
     * @param int $pagesAmo
     */
    public function setPagesAmo(int $pagesAmo)
    {
        $this->pagesAmo = $pagesAmo;
    }
}