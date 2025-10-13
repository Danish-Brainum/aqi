<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

if (!function_exists('getPaginatedResults')) {
    function getPaginatedResults($perPage, array $data, string $pageName = 'page'): LengthAwarePaginator
    {
        $perPage = (int) ($perPage ?: 10);
        if ($perPage < 1) {
            $perPage = 10;
        }

        // Use custom page name (e.g., results_page, deleted_page)
        $page = Paginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            collect($data)->forPage($page, $perPage),
            count($data),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }
}
