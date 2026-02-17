<?php

namespace Modules\Categories\App\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\CountryQueryBuilderTrait;
use Modules\Categories\App\Models\Category;

class CategoriesQuery
{
    use CountryQueryBuilderTrait;

    public function fetchCategories(array $filters = []): Collection
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';

        $query = $this->getCountryConnection()
            ->table('categories')
            ->select([
                'categories.id',
                'categories.icon',
            ])
            ->selectRaw("categories.{$nameColumn} as name")
            ->whereNull('categories.parent_id')
            ->where('categories.is_active', true)
            ->when(isset($filters['home_page_section_id']) && $filters['home_page_section_id'], function ($query) use ($filters) {
                $query->whereExists(function ($subQuery) use ($filters) {
                    $subQuery->select(DB::raw(1))
                        ->from('home_page_items')
                        ->whereColumn('home_page_items.item_id', 'categories.id')
                        ->where('home_page_items.item_type', Category::class)
                        ->where('home_page_items.home_page_id', $filters['home_page_section_id']);
                });
            });

        $this->applyCategoryVisibility($query);

        return $query->get();
    }

    private function applyCategoryVisibility($query): void
    {
        $user = auth('api')->user();
        if (! $user) {
            return;
        }

        $defaultAddress = $user->defaultAddress;
        if (! $defaultAddress) {
            return;
        }
        
        // old implementation
        // $regionId = $defaultAddress->region_id;
        // $query->whereExists(function ($subQuery) use ($defaultAddress, $regionId) {
        //     $subQuery->select(DB::raw(1))
        //         ->from('category_visibilities')
        //         ->whereColumn('category_visibilities.category_id', 'categories.id')
        //         ->where('category_visibilities.emirate_id', $defaultAddress->emirate_id)
        //         ->where(function ($regionQuery) use ($regionId) {
        //             $regionQuery->whereNull('category_visibilities.region_ids');
        //             if ($regionId !== null) {
        //                 $regionQuery->orWhereJsonContains('category_visibilities.region_ids', (int) $regionId);
        //             }
        //         });
        // });

        // new implementation
        applyIsVisibleVisibility($query, 'category_visibilities', 'category_id', 'categories.id', $defaultAddress);
    }
}
