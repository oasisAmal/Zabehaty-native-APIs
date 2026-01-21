<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\Shops\App\Models\Shop;
use Modules\DynamicCategories\App\Services\Builders\Concerns\UsesDynamicCategoriesQueryBuilder;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;

class ShopSectionBuilder implements SectionBuilderInterface
{
    use UsesDynamicCategoriesQueryBuilder;
    /**
     * Build shop section data
     *
     * @param array $dynamicCategorySection
     * @return array
     */
    public function build(array $dynamicCategorySection): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';

        $query = $this->buildItemsQuery($dynamicCategorySection, $nameColumn);

        $items = $query->limit(Pagination::PER_PAGE)->get();

        return $items
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image_url' => $item->banner ?? '',
                    'logo_url' => $item->image ?? '',
                    'rating' => $item->rating ? (float) $item->rating : null,
                    'category' => $item->first_parent_category_name ?? '',
                    'payment_badges' => ['tamara', 'tabby'],
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Ensure items are loaded without the costly visibility scope.
     */
    public function hasMoreItems(array $dynamicCategorySection): bool
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';

        $query = $this->buildItemsQuery($dynamicCategorySection, $nameColumn);

        return $query->count() > Pagination::PER_PAGE;
    }

    private function buildItemsQuery(array $dynamicCategorySection, string $nameColumn)
    {
        $query = $this->getConnection()
            ->table('dynamic_category_section_items')
            ->join('shops', function ($join) {
                $join->on('shops.id', '=', 'dynamic_category_section_items.item_id')
                    ->where('dynamic_category_section_items.item_type', Shop::class);
            })
            ->select([
                'shops.id',
                'shops.banner',
                'shops.image',
                'shops.rating',
            ])
            ->selectRaw("shops.{$nameColumn} as name")
            ->selectSub($this->firstParentCategorySubQuery($nameColumn), 'first_parent_category_name')
            ->where('dynamic_category_section_items.dynamic_category_section_id', $dynamicCategorySection['id'])
            ->where('shops.is_active', true)
            ->orderBy('dynamic_category_section_items.id');

        $this->applyShopVisibility($query);

        return $query;
    }

    private function firstParentCategorySubQuery(string $nameColumn)
    {
        return $this->getConnection()
            ->table('shop_categories')
            ->join('categories', 'categories.id', '=', 'shop_categories.category_id')
            ->selectRaw("categories.{$nameColumn}")
            ->whereColumn('shop_categories.shop_id', 'shops.id')
            ->whereNull('categories.parent_id')
            ->limit(1);
    }

    private function applyShopVisibility($query): void
    {
        $defaultAddress = $this->getDefaultAddress();
        if (! $defaultAddress) {
            return;
        }

        $this->applyShopVisibilityByShopId($query, 'shops.id', $defaultAddress);
        $this->applyCategoryVisibilityThroughShopCategories($query, 'shops.id', $defaultAddress);
    }
}

