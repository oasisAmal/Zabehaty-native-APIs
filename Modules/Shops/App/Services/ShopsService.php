<?php

namespace Modules\Shops\App\Services;

use App\Enums\Pagination;
use Modules\Shops\App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShopsService
{
    /**
     * Get shops with optional filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getShops(array $filters = []): LengthAwarePaginator
    {
        return Shop::when(isset($filters['home_page_section_id']) && $filters['home_page_section_id'], function (Builder $query) use ($filters) {
            return $query->whereHas('homePageItems', function (Builder $subQuery) use ($filters) {
                $subQuery->where('home_page_id', $filters['home_page_section_id']);
            });
        })->paginate(Pagination::PER_PAGE);
    }
}

