<?php

namespace App\Entities;

class PaginatedList extends Entity
{
    public $items;
    public $total; // total records
    public $pageSize;
    public $currentPage;
    public $totalPages;
    public $hasNext;
    public $hasPrevious;
    public $nextPage;
    public $previousPage;

    public function __construct($items, $total, $pageSize, $currentPage)
    {
        $this->items = $items;
        $this->total = $total;
        $this->pageSize = $pageSize;
        $this->currentPage = $currentPage;
        $this->totalPages = ceil($total / $pageSize);

        $this->hasNext = $currentPage < $this->totalPages;
        $this->hasPrevious = $currentPage > 1;
        $this->nextPage = min($currentPage + 1, $this->totalPages);
        $this->previousPage = max($currentPage - 1, 1);
    }

    /**
     * Summary of createFromQueryBuilder
     * @param mixed $queryBuilder
     * @param mixed $pageIndex zero-based page index
     * @param mixed $pageSize
     * @return PaginatedList
     */
    public static function createFromQueryBuilder($queryBuilder, $pageIndex, $pageSize)
    {
        $total = $queryBuilder->count();
        $items = $queryBuilder->offset($pageIndex * $pageSize)->limit($pageSize)->get();

        return new PaginatedList($items, $total, $pageSize, $pageIndex);
    }
}
