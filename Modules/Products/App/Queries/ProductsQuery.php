<?php

namespace Modules\Products\App\Queries;

use App\Enums\Pagination;
use Illuminate\Support\Facades\DB;
use App\Traits\CountryQueryBuilderTrait;
use Modules\Products\App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductsQuery
{
    use CountryQueryBuilderTrait;

    public function fetchProducts(array $filters = []): LengthAwarePaginator
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';
        $perPage = isset($filters['per_page']) && $filters['per_page'] ? $filters['per_page'] : Pagination::PER_PAGE;

        $user = auth('api')->user();
        
        $query = $this->getCountryConnection()
            ->table('products')
            ->leftJoin('shops', 'shops.id', '=', 'products.shop_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('favourites', function ($join) use ($user) {
                $join->on('favourites.product_id', '=', 'products.id');
                if ($user) {
                    $join->where('favourites.user_id', '=', $user->id);
                }
            })
            ->select([
                'products.id',
                'products.image',
                'products.price',
                'products.old_price',
                'products.has_sub_products',
                'products.limited_offer_expired_at',
            ])
            ->selectRaw('favourites.id as is_favorite')
            ->selectRaw("products.{$nameColumn} as name")
            ->selectRaw("shops.{$nameColumn} as shop_name")
            ->selectRaw("categories.{$nameColumn} as category_name")
            ->selectSub($this->minSubProductPriceSubQuery(), 'min_sub_price')
            ->selectSub($this->badgeNameSubQuery($nameColumn), 'badge_name')
            ->distinct()
            ->where('products.is_active', true)
            ->where('products.is_approved', true)
            ->whereNotNull('products.department_id')
            ->where(function ($query) {
                $query->whereNull('products.shop_id')
                    ->orWhereNotNull('shops.id');
            })
            ->where(function ($query) {
                $query->where('products.price', '>', 0)
                    ->orWhereExists($this->activeSubProductsExistsSubQuery());
            });

        $this->applyProductVisibility($query);

        $this->applyHomePageFilter($query, $filters);
        $this->applyDynamicCategoryFilters($query, $filters);
        $this->applyDynamicShopFilters($query, $filters);

        return $query->orderBy('products.id', 'desc')->paginate($perPage);
    }

    private function applyHomePageFilter($query, array $filters): void
    {
        if (! isset($filters['home_page_section_id']) || ! $filters['home_page_section_id']) {
            return;
        }

        $query->whereExists(function ($subQuery) use ($filters) {
            $subQuery->select(DB::raw(1))
                ->from('home_page_items')
                ->whereColumn('home_page_items.item_id', 'products.id')
                ->where('home_page_items.item_type', Product::class)
                ->where('home_page_items.home_page_id', $filters['home_page_section_id']);
        });
    }

    private function applyDynamicCategoryFilters($query, array $filters): void
    {
        $isAllMenuItem = false;

        if (isset($filters['dynamic_category_section_id'])) {
            $categoryId = $this->getCountryConnection()
                ->table('dynamic_category_sections')
                ->where('id', $filters['dynamic_category_section_id'])
                ->value('category_id');
            if ($categoryId) {
                $childCategoryIds = getAllChildCategoriesIds($categoryId);
                $query->whereIn('products.category_id', $childCategoryIds);
            }
        }

        if (isset($filters['dynamic_category_menu_id'])) {
            $isAllMenuItem = $this->getCountryConnection()
                ->table('dynamic_category_section_items')
                ->where('menu_item_parent_id', $filters['dynamic_category_menu_id'])
                ->where('is_all_menu_item', true)
                ->exists();
        }

        if (isset($filters['dynamic_category_section_id']) && $isAllMenuItem == false) {
            $query->whereExists(function ($subQuery) use ($filters) {
                $subQuery->select(DB::raw(1))
                    ->from('dynamic_category_section_items')
                    ->whereColumn('dynamic_category_section_items.item_id', 'products.id')
                    ->where('dynamic_category_section_items.item_type', Product::class)
                    ->where('dynamic_category_section_items.dynamic_category_section_id', $filters['dynamic_category_section_id']);
            });
        }

        if (isset($filters['dynamic_category_menu_id']) && $isAllMenuItem == false) {
            $query->whereExists(function ($subQuery) use ($filters) {
                $subQuery->select(DB::raw(1))
                    ->from('dynamic_category_section_items')
                    ->whereColumn('dynamic_category_section_items.item_id', 'products.id')
                    ->where('dynamic_category_section_items.item_type', Product::class)
                    ->where('dynamic_category_section_items.menu_item_parent_id', $filters['dynamic_category_menu_id']);
            });
        }
    }

    private function applyDynamicShopFilters($query, array $filters): void
    {
        $isAllMenuItem = false;

        if (isset($filters['dynamic_shop_section_id'])) {
            $shopId = $this->getCountryConnection()
                ->table('dynamic_shop_sections')
                ->where('id', $filters['dynamic_shop_section_id'])
                ->value('shop_id');
            if ($shopId) {
                $query->where('products.shop_id', $shopId);
            }
        }

        if (isset($filters['dynamic_shop_menu_id'])) {
            $isAllMenuItem = $this->getCountryConnection()
                ->table('dynamic_shop_section_items')
                ->where('menu_item_parent_id', $filters['dynamic_shop_menu_id'])
                ->where('is_all_menu_item', true)
                ->exists();
        }

        if (isset($filters['dynamic_shop_section_id']) && $isAllMenuItem == false) {
            $query->whereExists(function ($subQuery) use ($filters) {
                $subQuery->select(DB::raw(1))
                    ->from('dynamic_shop_section_items')
                    ->whereColumn('dynamic_shop_section_items.item_id', 'products.id')
                    ->where('dynamic_shop_section_items.item_type', Product::class)
                    ->where('dynamic_shop_section_items.dynamic_shop_section_id', $filters['dynamic_shop_section_id']);
            });
        }

        if (isset($filters['dynamic_shop_menu_id']) && $isAllMenuItem == false) {
            $query->whereExists(function ($subQuery) use ($filters) {
                $subQuery->select(DB::raw(1))
                    ->from('dynamic_shop_section_items')
                    ->whereColumn('dynamic_shop_section_items.item_id', 'products.id')
                    ->where('dynamic_shop_section_items.item_type', Product::class)
                    ->where('dynamic_shop_section_items.menu_item_parent_id', $filters['dynamic_shop_menu_id']);
            });
        }
    }

    private function minSubProductPriceSubQuery()
    {
        return $this->getCountryConnection()
            ->table('sub_products')
            ->selectRaw('MIN(sub_products.price)')
            ->whereColumn('sub_products.product_id', 'products.id')
            ->where('sub_products.is_active', true);
    }

    private function activeSubProductsExistsSubQuery()
    {
        return $this->getCountryConnection()
            ->table('sub_products')
            ->selectRaw('1')
            ->whereColumn('sub_products.product_id', 'products.id')
            ->where('sub_products.is_active', true);
    }

    private function badgeNameSubQuery(string $nameColumn)
    {
        return $this->getCountryConnection()
            ->table('product_badges')
            ->join('badges', 'badges.id', '=', 'product_badges.badge_id')
            ->selectRaw("badges.{$nameColumn}")
            ->whereColumn('product_badges.product_id', 'products.id')
            ->limit(1);
    }

    private function applyProductVisibility($query): void
    {
        $user = auth('api')->user();
        if (! $user) {
            return;
        }

        $defaultAddress = $user->defaultAddress;
        if (! $defaultAddress) {
            return;
        }

        $regionId = $defaultAddress->region_id;

        $query->whereExists(function ($subQuery) use ($defaultAddress, $regionId) {
            $subQuery->select(DB::raw(1))
                ->from('product_visibilities')
                ->whereColumn('product_visibilities.product_id', 'products.id')
                ->where('product_visibilities.emirate_id', $defaultAddress->emirate_id)
                ->where(function ($regionQuery) use ($regionId) {
                    $regionQuery->whereNull('product_visibilities.region_ids');
                    if ($regionId !== null) {
                        $regionQuery->orWhereJsonContains('product_visibilities.region_ids', (int) $regionId);
                    }
                });
        });

        $query->where(function ($shopQuery) use ($defaultAddress, $regionId) {
            $shopQuery->whereNull('products.shop_id')
                ->orWhereExists(function ($subQuery) use ($defaultAddress, $regionId) {
                    $subQuery->select(DB::raw(1))
                        ->from('shop_visibilities')
                        ->whereColumn('shop_visibilities.shop_id', 'products.shop_id')
                        ->where('shop_visibilities.emirate_id', $defaultAddress->emirate_id)
                        ->where(function ($regionQuery) use ($regionId) {
                            $regionQuery->whereNull('shop_visibilities.region_ids');
                            if ($regionId !== null) {
                                $regionQuery->orWhereJsonContains('shop_visibilities.region_ids', (int) $regionId);
                            }
                        });
                });
        });

        $query->whereExists(function ($subQuery) use ($defaultAddress, $regionId) {
            $subQuery->select(DB::raw(1))
                ->from('category_visibilities')
                ->whereColumn('category_visibilities.category_id', 'products.category_id')
                ->where('category_visibilities.emirate_id', $defaultAddress->emirate_id)
                ->where(function ($regionQuery) use ($regionId) {
                    $regionQuery->whereNull('category_visibilities.region_ids');
                    if ($regionId !== null) {
                        $regionQuery->orWhereJsonContains('category_visibilities.region_ids', (int) $regionId);
                    }
                });
        });
    }
}
