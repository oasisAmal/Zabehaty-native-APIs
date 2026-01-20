<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Illuminate\Support\Facades\DB;
use Modules\Shops\App\Models\Shop;
use Modules\HomePage\App\Services\Builders\Concerns\UsesHomepageQueryBuilder;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class ShopSectionBuilder implements SectionBuilderInterface
{
    use UsesHomepageQueryBuilder;
    /**
     * Build shop section data
     *
     * @param array $homePage
     * @return array
     */
    public function build(array $homePage): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';

        $query = $this->getConnection()
            ->table('home_page_items')
            ->join('shops', function ($join) {
                $join->on('shops.id', '=', 'home_page_items.item_id')
                    ->where('home_page_items.item_type', Shop::class);
            })
            ->select([
                'shops.id',
                'shops.banner',
                'shops.image',
                'shops.rating',
            ])
            ->selectRaw("shops.{$nameColumn} as name")
            ->selectSub($this->firstParentCategorySubQuery($nameColumn), 'first_parent_category_name')
            ->where('home_page_items.home_page_id', $homePage['id'])
            ->where('shops.is_active', true)
            ->orderBy('home_page_items.id')
            ->limit(Pagination::PER_PAGE);

        $this->applyShopVisibility($query);

        $items = $query->get();

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
            ->toArray();
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
