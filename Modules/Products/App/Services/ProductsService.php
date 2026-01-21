<?php

namespace Modules\Products\App\Services;

use Modules\Products\App\Queries\ProductsQuery;
use Modules\Products\App\Queries\ProductDetailsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductsService
{
    protected ProductsQuery $productsQuery;
    protected ProductDetailsQuery $productDetailsQuery;

    public function __construct(ProductsQuery $productsQuery, ProductDetailsQuery $productDetailsQuery)
    {
        $this->productsQuery = $productsQuery;
        $this->productDetailsQuery = $productDetailsQuery;
    }

    /**
     * Get products with optional filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getProducts(array $filters = []): LengthAwarePaginator
    {
        return $this->productsQuery->fetchProducts($filters);
    }

    /**
     * Get product details
     *
     * @param int $id
     * @return Product|null
     */
    public function getProductDetails(int $id): ?array
    {
        return $this->productDetailsQuery->fetchProductDetails($id);
    }
}
