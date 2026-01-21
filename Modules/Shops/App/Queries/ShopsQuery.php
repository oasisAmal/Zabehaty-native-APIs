<?php

namespace Modules\Shops\App\Queries;

use App\Enums\Pagination;
use App\Traits\CountryQueryBuilderTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Shops\App\Models\Shop;

class ShopsQuery
{
    use CountryQueryBuilderTrait;

    public function fetchShops(array $filters = []): LengthAwarePaginator
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';
        $perPage = isset($filters['per_page']) && $filters['per_page'] ? $filters['per_page'] : Pagination::PER_PAGE;

        $query = $this->getCountryConnection()
            ->table('shops')
            ->select([
                'shops.id',
                'shops.banner',
                'shops.image',
                'shops.rating',
            ])
            ->selectRaw("shops.{$nameColumn} as name")
            ->selectSub($this->firstParentCategorySubQuery($nameColumn), 'first_parent_category_name')
            ->where('shops.is_active', true);

        $this->applyShopVisibility($query);

        $query->when(isset($filters['home_page_section_id']) && $filters['home_page_section_id'], function ($query) use ($filters) {
            $query->whereExists(function ($subQuery) use ($filters) {
                $subQuery->select(DB::raw(1))
                    ->from('home_page_items')
                    ->whereColumn('home_page_items.item_id', 'shops.id')
                    ->where('home_page_items.item_type', Shop::class)
                    ->where('home_page_items.home_page_id', $filters['home_page_section_id']);
            });
        })->when(
            isset($filters['dynamic_category_section_id']) && $filters['dynamic_category_section_id'] && !$filters['is_all_menu_item'],
            function ($query) use ($filters) {
                $query->whereExists(function ($subQuery) use ($filters) {
                    $subQuery->select(DB::raw(1))
                        ->from('dynamic_category_section_items')
                        ->whereColumn('dynamic_category_section_items.item_id', 'shops.id')
                        ->where('dynamic_category_section_items.item_type', Shop::class)
                        ->where('dynamic_category_section_items.dynamic_category_section_id', $filters['dynamic_category_section_id']);
                });
            }
        )->when(
            isset($filters['dynamic_category_menu_id']) && $filters['dynamic_category_menu_id'] && !$filters['is_all_menu_item'],
            function ($query) use ($filters) {
                $query->whereExists(function ($subQuery) use ($filters) {
                    $subQuery->select(DB::raw(1))
                        ->from('dynamic_category_section_items')
                        ->whereColumn('dynamic_category_section_items.item_id', 'shops.id')
                        ->where('dynamic_category_section_items.item_type', Shop::class)
                        ->where('dynamic_category_section_items.menu_item_parent_id', $filters['dynamic_category_menu_id']);
                });
            }
        );

        return $query->paginate($perPage);
    }

    private function firstParentCategorySubQuery(string $nameColumn)
    {
        return $this->getCountryConnection()
            ->table('shop_categories')
            ->join('categories', 'categories.id', '=', 'shop_categories.category_id')
            ->selectRaw("categories.{$nameColumn}")
            ->whereColumn('shop_categories.shop_id', 'shops.id')
            ->whereNull('categories.parent_id')
            ->limit(1);
    }

    private function applyShopVisibility($query): void
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
                ->from('shop_visibilities')
                ->whereColumn('shop_visibilities.shop_id', 'shops.id')
                ->where('shop_visibilities.emirate_id', $defaultAddress->emirate_id)
                ->where(function ($regionQuery) use ($regionId) {
                    $regionQuery->whereNull('shop_visibilities.region_ids');
                    if ($regionId !== null) {
                        $regionQuery->orWhereJsonContains('shop_visibilities.region_ids', (int) $regionId);
                    }
                });
        });

        $query->whereExists(function ($subQuery) use ($defaultAddress, $regionId) {
            $subQuery->select(DB::raw(1))
                ->from('shop_categories')
                ->join('category_visibilities', 'category_visibilities.category_id', '=', 'shop_categories.category_id')
                ->whereColumn('shop_categories.shop_id', 'shops.id')
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
