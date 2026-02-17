<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Enums\CountryCurrencies;
use Modules\Products\App\Models\Product;
use Modules\DynamicCategories\App\Services\Builders\Concerns\UsesDynamicCategoriesQueryBuilder;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;

class ProductSectionBuilder implements SectionBuilderInterface
{
    use UsesDynamicCategoriesQueryBuilder;
    /**
     * Build product section data
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
                $price = $this->resolvePrice($item);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image_url' => $item->image ?? null,
                    'shop' => $item->shop_name,
                    'category' => $item->category_name,
                    'currency' => CountryCurrencies::getCurrency(),
                    'price' => $price,
                    'price_before_discount' => $item->old_price ? (float) $item->old_price : null,
                    'discount_percentage' => $this->resolveDiscountPercentage($item->old_price, $price),
                    'limited_offer_expired_at' => $this->resolveExpiredAtTimestamp($item->limited_offer_expired_at),
                    'badge' => $item->badge_name ?? null,
                    'is_favorite' => (bool) ($item->is_favorite ?? false),
                ];
            })
            ->values()
            ->toArray();
    }

    public function hasMoreItems(array $dynamicCategorySection): bool
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';

        $query = $this->buildItemsQuery($dynamicCategorySection, $nameColumn);

        return $query->count() > Pagination::PER_PAGE;
    }

    private function buildItemsQuery(array $dynamicCategorySection, string $nameColumn)
    {
        $user = auth('api')->user();

        $query = $this->getConnection()
            ->table('dynamic_category_section_items')
            ->join('products', function ($join) {
                $join->on('products.id', '=', 'dynamic_category_section_items.item_id')
                    ->where('dynamic_category_section_items.item_type', Product::class);
            })
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
            ->selectRaw('IF(favourites.id IS NULL, 0, 1) as is_favorite')
            ->selectRaw("products.{$nameColumn} as name")
            ->selectRaw("shops.{$nameColumn} as shop_name")
            ->selectRaw("categories.{$nameColumn} as category_name")
            ->selectSub($this->minSubProductPriceSubQuery(), 'min_sub_price')
            ->selectSub($this->badgeNameSubQuery($nameColumn), 'badge_name')
            ->where('dynamic_category_section_items.dynamic_category_section_id', $dynamicCategorySection['id'])
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
            })
            ->orderBy('dynamic_category_section_items.id');

        $this->applyProductVisibility($query);

        return $query;
    }

    private function minSubProductPriceSubQuery()
    {
        return $this->getConnection()
            ->table('sub_products')
            ->selectRaw('MIN(sub_products.price)')
            ->whereColumn('sub_products.product_id', 'products.id')
            ->where('sub_products.is_active', true);
    }

    private function activeSubProductsExistsSubQuery()
    {
        return $this->getConnection()
            ->table('sub_products')
            ->selectRaw('1')
            ->whereColumn('sub_products.product_id', 'products.id')
            ->where('sub_products.is_active', true);
    }

    private function badgeNameSubQuery(string $nameColumn)
    {
        return $this->getConnection()
            ->table('product_badges')
            ->join('badges', 'badges.id', '=', 'product_badges.badge_id')
            ->selectRaw("badges.{$nameColumn}")
            ->whereColumn('product_badges.product_id', 'products.id')
            ->limit(1);
    }

    private function resolvePrice($item): float
    {
        $minSubPrice = $item->min_sub_price !== null ? (float) $item->min_sub_price : null;
        if ((bool) $item->has_sub_products && $minSubPrice !== null) {
            return $minSubPrice;
        }

        return (float) $item->price;
    }

    private function resolveDiscountPercentage($oldPrice, float $price): ?float
    {
        if (! $oldPrice) {
            return null;
        }

        return (float) discountCalc($oldPrice, $price);
    }

    private function resolveExpiredAtTimestamp($value): ?int
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->timestamp;
    }

    private function applyProductVisibility($query): void
    {
        $defaultAddress = $this->getDefaultAddress();
        if (! $defaultAddress) {
            return;
        }

        // old implementation
        // $this->applyVisibilityExists($query, 'product_visibilities', 'product_id', 'products.id', $defaultAddress);

        // $query->where(function ($shopQuery) use ($defaultAddress) {
        //     $shopQuery->whereNull('products.shop_id')
        //         ->orWhere(function ($shopVisibilityQuery) use ($defaultAddress) {
        //             $this->applyShopVisibilityByShopId(
        //                 $shopVisibilityQuery,
        //                 'products.shop_id',
        //                 $defaultAddress
        //             );
        //         });
        // });

        // $this->applyCategoryVisibilityByCategoryId($query, 'products.category_id', $defaultAddress);

        // new implementation
        applyIsVisibleVisibility($query, 'product_visibilities', 'product_id', 'products.id', $defaultAddress);
    }
}
