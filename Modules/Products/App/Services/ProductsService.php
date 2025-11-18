<?php

namespace Modules\Products\App\Services;

use App\Enums\Pagination;
use Modules\Products\App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductsService
{
    /**
     * Get products with optional filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getProducts(array $filters = []): LengthAwarePaginator
    {
        return Product::when(isset($filters['home_page_section_id']) && $filters['home_page_section_id'], function (Builder $query) use ($filters) {
            return $query->whereHas('homePageItems', function (Builder $subQuery) use ($filters) {
                $subQuery->where('home_page_id', $filters['home_page_section_id']);
            });
        })->paginate(Pagination::PER_PAGE);
    }
}
