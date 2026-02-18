<?php

namespace Modules\Search\App\Services\Builders\Sections;

use Illuminate\Support\Carbon;
use App\Enums\CountryCurrencies;
use Modules\Search\App\Services\Builders\Concerns\UsesSearchQueryBuilder;
use Modules\Search\App\Services\Builders\Interfaces\SectionBuilderInterface;

class RecentlyViewedProductsSectionBuilder implements SectionBuilderInterface
{
    use UsesSearchQueryBuilder;

    /**
     * Build recently viewed products section data
     *
     * @return array
     */
    public function build(): array
    {
        $user = auth('api')->user();
        if (! $user) {
            return [];
        }

        $productIds = $this->getCountryConnection()
            ->table('user_visits')
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(20)
            ->pluck('product_id')
            ->toArray();

        return $this->fetchProductsByIds($productIds);
    }

    private function fetchProductsByIds(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter($productIds)));
        if (empty($productIds)) {
            return [];
        }

        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';

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
            ->selectRaw('IF(favourites.id IS NULL, 0, 1) as is_favorite')
            ->selectRaw("products.{$nameColumn} as name")
            ->selectRaw("shops.{$nameColumn} as shop_name")
            ->selectRaw("categories.{$nameColumn} as category_name")
            ->selectSub($this->minSubProductPriceSubQuery(), 'min_sub_price')
            ->selectSub($this->badgeNameSubQuery($nameColumn), 'badge_name')
            ->selectSub($this->hasAddonsSubQuery(), 'has_addons')
            ->whereIn('products.id', $productIds)
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

        $query->orderByRaw('FIELD(products.id, ' . implode(',', $productIds) . ')');

        return $query
            ->get()
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
                    'is_favorite' => (bool) $item->is_favorite,
                    'has_addons' => (bool) $item->has_addons,
                ];
            })
            ->values()
            ->toArray();
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

    private function hasAddonsSubQuery()
    {
        return $this->getCountryConnection()
            ->table('product_addon_sections')
            ->selectRaw('1')
            ->whereColumn('product_addon_sections.product_id', 'products.id')
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

        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return Carbon::parse($value)->timestamp;
    }

    private function applyProductVisibility($query): void
    {
        $defaultAddress = $this->getDefaultAddress();
        if (! $defaultAddress) {
            return;
        }

        $this->applyVisibilityExists($query, 'product_visibilities', 'product_id', 'products.id', $defaultAddress);

        $query->where(function ($shopQuery) use ($defaultAddress) {
            $shopQuery->whereNull('products.shop_id')
                ->orWhere(function ($shopVisibilityQuery) use ($defaultAddress) {
                    $this->applyVisibilityExists(
                        $shopVisibilityQuery,
                        'shop_visibilities',
                        'shop_id',
                        'products.shop_id',
                        $defaultAddress
                    );
                });
        });

        $this->applyVisibilityExists($query, 'category_visibilities', 'category_id', 'products.category_id', $defaultAddress);
    }
}
