<?php

namespace Modules\DynamicCategories\App\Services\Builders\Concerns;

use App\Traits\CountryQueryBuilderTrait;

trait UsesDynamicCategoriesQueryBuilder
{
    use CountryQueryBuilderTrait;

    private function getConnection()
    {
        return $this->getCountryConnection();
    }

    private function getDefaultAddress(): ?object
    {
        $user = auth('api')->user();
        if (! $user) {
            return null;
        }

        return $user->defaultAddress ?: null;
    }

    private function applyVisibilityExists(
        $query,
        string $visibilityTable,
        string $entityColumn,
        string $entityIdColumn,
        object $defaultAddress
    ): void {
        $regionId = $defaultAddress->region_id;

        $query->whereExists(function ($subQuery) use ($visibilityTable, $entityColumn, $entityIdColumn, $defaultAddress, $regionId) {
            $subQuery->selectRaw('1')
                ->from($visibilityTable)
                ->whereColumn("{$visibilityTable}.{$entityColumn}", $entityIdColumn)
                ->where("{$visibilityTable}.emirate_id", $defaultAddress->emirate_id)
                ->where(function ($regionQuery) use ($visibilityTable, $regionId) {
                    $regionQuery->whereNull("{$visibilityTable}.region_ids");
                    if ($regionId !== null) {
                        $regionQuery->orWhereJsonContains("{$visibilityTable}.region_ids", (int) $regionId);
                    }
                });
        });
    }

    private function applyCategoryVisibilityByCategoryId($query, string $categoryIdColumn, object $defaultAddress): void
    {
        $this->applyVisibilityExists(
            $query,
            'category_visibilities',
            'category_id',
            $categoryIdColumn,
            $defaultAddress
        );
    }

    private function applyShopVisibilityByShopId($query, string $shopIdColumn, object $defaultAddress): void
    {
        $this->applyVisibilityExists(
            $query,
            'shop_visibilities',
            'shop_id',
            $shopIdColumn,
            $defaultAddress
        );
    }

    private function applyCategoryVisibilityThroughShopCategories(
        $query,
        string $shopIdColumn,
        object $defaultAddress
    ): void {
        $regionId = $defaultAddress->region_id;

        $query->whereExists(function ($subQuery) use ($shopIdColumn, $defaultAddress, $regionId) {
            $subQuery->selectRaw('1')
                ->from('shop_categories')
                ->join('category_visibilities', 'category_visibilities.category_id', '=', 'shop_categories.category_id')
                ->whereColumn('shop_categories.shop_id', $shopIdColumn)
                ->where('category_visibilities.emirate_id', $defaultAddress->emirate_id)
                ->where(function ($regionQuery) use ($regionId) {
                    $regionQuery->whereNull('category_visibilities.region_ids');
                    if ($regionId !== null) {
                        $regionQuery->orWhereJsonContains('category_visibilities.region_ids', (int) $regionId);
                    }
                });
        });
    }

    private function applyCategoryVisibilityThroughProducts(
        $query,
        string $productIdColumn,
        object $defaultAddress
    ): void {
        $regionId = $defaultAddress->region_id;

        $query->whereExists(function ($subQuery) use ($productIdColumn, $defaultAddress, $regionId) {
            $subQuery->selectRaw('1')
                ->from('products')
                ->join('category_visibilities', 'category_visibilities.category_id', '=', 'products.category_id')
                ->whereColumn('products.id', $productIdColumn)
                ->where('category_visibilities.emirate_id', $defaultAddress->emirate_id)
                ->where(function ($regionQuery) use ($regionId) {
                    $regionQuery->whereNull('category_visibilities.region_ids');
                    if ($regionId !== null) {
                        $regionQuery->orWhereJsonContains('category_visibilities.region_ids', (int) $regionId);
                    }
                });
        });
    }

    private function applyShopVisibilityThroughProducts(
        $query,
        string $productIdColumn,
        object $defaultAddress
    ): void {
        $regionId = $defaultAddress->region_id;

        $query->whereExists(function ($subQuery) use ($productIdColumn, $defaultAddress, $regionId) {
            $subQuery->selectRaw('1')
                ->from('products')
                ->join('shop_visibilities', 'shop_visibilities.shop_id', '=', 'products.shop_id')
                ->whereColumn('products.id', $productIdColumn)
                ->where('shop_visibilities.emirate_id', $defaultAddress->emirate_id)
                ->where(function ($regionQuery) use ($regionId) {
                    $regionQuery->whereNull('shop_visibilities.region_ids');
                    if ($regionId !== null) {
                        $regionQuery->orWhereJsonContains('shop_visibilities.region_ids', (int) $regionId);
                    }
                });
        });
    }

    private function applyIsVisibleVisibility(
        $query,
        string $visibilityTable,
        string $visibilityFkColumn,
        string $mainEntityIdColumn,
        object $defaultAddress
    ): void {
        applyIsVisibleVisibility($query, $visibilityTable, $visibilityFkColumn, $mainEntityIdColumn, $defaultAddress);
    }

    private function applyIsVisibleVisibilityExists(
        $query,
        string $visibilityTable,
        string $visibilityFkColumn,
        string $mainEntityIdColumn,
        object $defaultAddress
    ): void {
        $query->whereExists(function ($subQuery) use ($visibilityTable, $visibilityFkColumn, $mainEntityIdColumn, $defaultAddress) {
            $subQuery->selectRaw('1')
                ->from($visibilityTable)
                ->whereColumn("{$visibilityTable}.{$visibilityFkColumn}", $mainEntityIdColumn)
                ->where("{$visibilityTable}.emirate_id", $defaultAddress->emirate_id)
                ->whereJsonContains("{$visibilityTable}.region_ids", (int) $defaultAddress->region_id)
                ->where("{$visibilityTable}.is_visible", 1);
        });
    }
}
