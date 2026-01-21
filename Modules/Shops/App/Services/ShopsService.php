<?php

namespace Modules\Shops\App\Services;

use Modules\Shops\App\Queries\ShopsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShopsService
{
    protected ShopsQuery $shopsQuery;

    public function __construct(ShopsQuery $shopsQuery)
    {
        $this->shopsQuery = $shopsQuery;
    }

    /**
     * Get shops with optional filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getShops(array $filters = []): LengthAwarePaginator
    {
        return $this->shopsQuery->fetchShops($filters);
    }
}
