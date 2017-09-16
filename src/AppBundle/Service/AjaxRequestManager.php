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
    private $filterColumn;
    private $filterPattern;
    private $pagesAmo;

    public function __construct()
    {
        $this->page = 0;
        $this->rowsPerPage = 0;
        $this->isSorted = false;
        $this->isAscendingSort = false;
        $this->sortColumn = null;
        $this->isFiltered = false;
        $this->filterColumn = null;
        $this->filterPattern = null;
        $this->pagesAmo = 0;
    }

    public function parseRequestParams(Request $request): bool
    {
        if (!$request->query->has('rowsamo')) {
            return false;
        }
        $this->rowsPerPage = $request->query->get('rowsamo');
        if ($request->query->has('page')) {
            $this->page = $request->query->get('page');
        } else {
            $this->page = 1;
        }
        if ($request->query->has('sortbyfield') && $request->query->has('order')) {
            $this->isSorted = true;
            $this->sortColumn = $request->query->get('sortbyfield');
            if ($request->query->get('order') === 'asc') {
                $this->isAscendingSort = true;
            } else {
                $this->isAscendingSort = false;
            }
        }
        if ($request->query->has('filterbyfield') && $request->query->has('pattern')) {
            $this->isFiltered = true;
            $this->filterColumn = $request->query->get('filterbyfield');
            $this->filterPattern = $request->query->get('pattern');
        }
        return true;
    }

    public function getFilterColumn():? string
    {
        return $this->filterColumn;
    }

    public function getFilterPattern():? string
    {
        return $this->filterPattern;
    }

    public function isFiltered(): bool
    {
        return $this->isFiltered;
    }

    public function isAscendingSort(): bool
    {
        return $this->isAscendingSort;
    }

    public function isSorted(): bool
    {
        return $this->isSorted;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }

    public function getSortColumn(): int
    {
        return $this->sortColumn;
    }

    public function getPagesAmo(): int
    {
        return $this->pagesAmo;
    }

    public function setPagesAmo(int $pagesAmo)
    {
        $this->pagesAmo = $pagesAmo;
    }
}