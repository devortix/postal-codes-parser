<?php

namespace App\Helpers;

class PaginatorHelper
{
    public static function getOffset(int $limit, int $currentPage): int
    {
        return ($currentPage - 1) * $limit;
    }

    public static function getPagination(int $limit, int $currentPage, int $totalItems): array
    {
        $totalPages = (int)ceil($totalItems / $limit);

        return compact('currentPage', 'totalPages', 'totalItems', 'limit');
    }
}