<?php

namespace Modules\Categories\App\Services;

use Illuminate\Support\Collection;
use Modules\Categories\App\Queries\CategoriesQuery;

class CategoriesService
{
    protected CategoriesQuery $categoriesQuery;

    public function __construct(CategoriesQuery $categoriesQuery)
    {
        $this->categoriesQuery = $categoriesQuery;
    }

    /**
     * Get categories with optional filters.
     */
    public function getCategories(array $filters = []): Collection
    {
        return $this->categoriesQuery->fetchCategories($filters);
    }
}

