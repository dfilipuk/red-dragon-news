<?php

namespace AppBundle\Service;


use Symfony\Component\HttpFoundation\Request;

class AjaxRequestManager
{
    public const PAGINATION = 0;
    public const PAGINATION_SORT = 1;
    public const PAGINATION_FILTER = 2;
    public const PAGINATION_SORT_FILTER = 3;

    private $page;
    private $rowsPerPage;
    private $isSorted;
    private $isAscendingSort;
    private $sortColumn;
    private $isFiltered;
    private $filters;
    private $pagesAmo;
    private $queryType;

    public function __construct()
    {
        $this->queryType = self::PAGINATION;
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

        $f = fopen('/home/dmitry/Documents/log.txt', 'a');
        $arr = print_r($filters,1);
        fwrite($f, $arr);
        fclose($f);

        $this->identifyQueryType();
        return true;
    }

    private function identifyQueryType()
    {
        if ($this->isSorted && $this->isFiltered) {
            $this->queryType = self::PAGINATION_SORT_FILTER;
        } elseif ($this->isSorted && !$this->isFiltered) {
            $this->queryType = self::PAGINATION_SORT;
        } elseif (!$this->isSorted && $this->isFiltered) {
            $this->queryType = self::PAGINATION_FILTER;
        }
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function isAscendingSort(): bool
    {
        return $this->isAscendingSort;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }

    public function getSortColumn(): string
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

    public function getQueryType(): int
    {
        return $this->queryType;
    }
}