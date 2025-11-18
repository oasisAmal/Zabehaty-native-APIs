<?php

namespace Modules\Categories\App\Services;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Modules\Categories\App\Models\Category;

class CategoriesService
{
    /**
     * Get categories with optional filters.
     */
    public function getCategories(array $filters = []): Collection
    {
        return Category::onlyParents()->when(isset($filters['home_page_section_id']) && $filters['home_page_section_id'], function (Builder $query) use ($filters) {
            return $query->whereHas('homePageItems', function (Builder $subQuery) use ($filters) {
                $subQuery->where('home_page_id', $filters['home_page_section_id']);
            });
        })->get();
    }
}

