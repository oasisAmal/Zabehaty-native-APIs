<?php

namespace Modules\Products\App\Services;

use App\Traits\CountryQueryBuilderTrait;
use Modules\Products\App\Queries\ProductsQuery;
use Modules\Products\App\Queries\ProductDetailsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductsService
{
    use CountryQueryBuilderTrait;

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

    /**
     * Add or remove product from user favorites.
     *
     * @param array $data ['product_id' => int, 'is_favorite' => bool]
     * @return array{is_favorite: bool}
     */
    public function addRemoveFavorite(array $data): array
    {
        $userId = auth('api')->id();
        $productId = (int) $data['product_id'];
        $isFavorite = (bool) $data['is_favorite'];

        $table = $this->getCountryConnection()->table('favourites');

        if ($isFavorite) {
            $table->updateOrInsert(
                [
                    'user_id' => $userId,
                    'product_id' => $productId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            return ['is_favorite' => true];
        }

        $table->where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();

        return ['is_favorite' => false];
    }
}
