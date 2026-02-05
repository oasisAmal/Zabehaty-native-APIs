<?php

namespace Modules\Products\App\Queries;

use App\Traits\CountryQueryBuilderTrait;

class ProductDetailsQuery
{
    use CountryQueryBuilderTrait;

    public function fetchProductDetails(int $id): ?array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';
        $titleColumn = $locale === 'ar' ? 'title' : 'title_en';
        $briefColumn = $locale === 'ar' ? 'brief' : 'brief_en';
        $descriptionColumn = $locale === 'ar' ? 'description' : 'description_en';

        $product = $this->getCountryConnection()
            ->table('products')
            ->leftJoin('shops', 'shops.id', '=', 'products.shop_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select([
                'products.id',
                'products.category_id',
                'products.shop_id',
                'products.image',
                'products.images',
                'products.video',
                'products.price',
                'products.old_price',
                'products.has_sub_products',
                'products.limited_offer_expired_at',
                'products.has_quantity',
                'products.quantity_min',
                'products.quantity_step',
                'products.stock',
                'products.brief as brief_ar',
                'products.brief_en',
                'products.description as description_ar',
                'products.description_en',
            ])
            ->selectRaw("products.{$nameColumn} as name")
            ->selectRaw("shops.{$nameColumn} as shop_name")
            ->selectRaw("categories.{$nameColumn} as category_name")
            ->selectSub($this->minSubProductPriceSubQuery(), 'min_sub_price')
            ->selectSub($this->badgeNameSubQuery($nameColumn), 'badge_name')
            ->where('products.id', $id)
            ->first();

        if (! $product) {
            return null;
        }

        $productArray = (array) $product;
        $subProducts = $this->fetchSubProducts($id);
        $categoryId = $productArray['category_id'] ?? null;
        if ($categoryId !== null) {
            $subProducts = collect($subProducts)
                ->map(function ($subProduct) use ($categoryId) {
                    $subProduct->category_id = $categoryId;
                    return $subProduct;
                })
                ->toArray();
        }
        $productArray['sub_products'] = $subProducts;
        $productArray['addon_sections'] = $this->fetchAddonSections($id, $titleColumn);
        $productArray['product_cookings'] = $this->fetchProductCookings($id);
        $productArray['description_title'] = $this->getTranslationWithFallback(
            $productArray,
            $locale,
            'brief_ar',
            'brief_en'
        );
        $productArray['description'] = $this->getTranslationWithFallback(
            $productArray,
            $locale,
            'description_ar',
            'description_en'
        );
        $productArray['images'] = $this->resolveImages(images: $productArray['images'] ?? null, video: $productArray['video'] ?? null);
        $productArray['price'] = $this->resolvePrice($productArray);
        $productArray['price_before_discount'] = $productArray['old_price'] ? (float) $productArray['old_price'] : null;
        $productArray['discount_percentage'] = $this->resolveDiscountPercentage(
            $productArray['old_price'] ?? null,
            $productArray['price']
        );
        $productArray['limited_offer_expired_at'] = $this->resolveExpiredAtTimestamp(
            $productArray['limited_offer_expired_at'] ?? null
        );
        $productArray['badge'] = $productArray['badge_name'] ?? null;
        $productArray['is_favorite'] = false;
        $productArray['quantity_settings'] = [
            'min' => isset($productArray['quantity_min']) ? (float) $productArray['quantity_min'] : 0.0,
            'step' => isset($productArray['quantity_step']) ? (float) $productArray['quantity_step'] : 0.0,
        ];
        $productArray['stock'] = $this->resolveStockSettings($productArray);
        $productArray['size_section_name'] = $this->resolveSizeSectionName($productArray);

        saveUserVisit(productId: $productArray['id'] ?? null, shopId: $productArray['shop_id'] ?? null, categoryId: $productArray['category_id'] ?? null);

        return $productArray;
    }

    private function fetchSubProducts(int $productId): array
    {
        return $this->getCountryConnection()
            ->table('sub_products')
            ->select([
                'id',
                'name',
                'image',
                'price',
                'old_price',
                'notes',
                'stock',
                'enough_for_from',
                'enough_for_to',
                'data',
            ])
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->orderByRaw("CAST(SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(data, '$.weight')), '-', 1) AS UNSIGNED)")
            ->orderBy('price')
            ->get()
            ->map(function ($row) {
                $row->data = $row->data ? json_decode($row->data, true) : null;
                return $row;
            })
            ->toArray();
    }

    private function fetchAddonSections(int $productId, string $titleColumn): array
    {
        $sections = $this->getCountryConnection()
            ->table('addon_sections')
            ->join('product_addon_sections', 'product_addon_sections.addon_section_id', '=', 'addon_sections.id')
            ->select([
                'addon_sections.id',
                'addon_sections.type',
                'product_addon_sections.is_required',
                'product_addon_sections.id as pivot_id',
            ])
            ->selectRaw("addon_sections.{$titleColumn} as title")
            ->where('product_addon_sections.product_id', $productId)
            ->get();

        if ($sections->isEmpty()) {
            return [];
        }

        $pivotIds = $sections->pluck('pivot_id')->all();
        $items = $this->fetchAddonSectionItems($pivotIds, $titleColumn);

        return $sections->map(function ($section) use ($items) {
            $sectionItems = $items->get($section->pivot_id, collect());
            return [
                'id' => $section->id,
                'title' => $section->title,
                'type' => $section->type,
                'is_required' => (bool) $section->is_required,
                'items' => $sectionItems->values()->toArray(),
            ];
        })->toArray();
    }

    private function fetchAddonSectionItems(array $pivotIds, string $titleColumn)
    {
        $items = $this->getCountryConnection()
            ->table('product_addon_section_items')
            ->join('addon_section_items', 'addon_section_items.id', '=', 'product_addon_section_items.addon_section_item_id')
            ->select([
                'product_addon_section_items.product_addon_section_id',
                'addon_section_items.id',
                'addon_section_items.image',
                'product_addon_section_items.price',
                'addon_section_items.media',
            ])
            ->selectRaw("addon_section_items.{$titleColumn} as title")
            ->whereIn('product_addon_section_items.product_addon_section_id', $pivotIds)
            ->get()
            ->map(function ($row) {
                $row->media = handleMediaVideoOrImage($row->media);
                return $row;
            })
            ->groupBy('product_addon_section_id');

        return $items;
    }

    private function fetchProductCookings(int $productId): array
    {
        return $this->getCountryConnection()
            ->table('product_cookings')
            ->select(['id', 'product_id'])
            ->where('product_id', $productId)
            ->get()
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

    private function badgeNameSubQuery(string $nameColumn)
    {
        return $this->getCountryConnection()
            ->table('product_badges')
            ->join('badges', 'badges.id', '=', 'product_badges.badge_id')
            ->selectRaw("badges.{$nameColumn}")
            ->whereColumn('product_badges.product_id', 'products.id')
            ->limit(1);
    }

    private function getTranslationWithFallback(array $product, string $locale, string $primaryKey, string $fallbackKey): string
    {
        $primary = $product[$primaryKey] ?? '';
        $fallback = $product[$fallbackKey] ?? '';

        return $primary ?: $fallback;
    }

    private function resolveImages($images = null, $video = null): array
    {
        $decoded = $images ? (is_array($images) ? $images : (array) json_decode($images, true)) : [];
        $images = handleMediaVideoOrImage($decoded);
        if ($video) {
            $images[] = [
                'media_type' => 'video',
                'media_url' => $video,
            ];
        }
        return $images;
    }

    private function resolvePrice(array $product): float
    {
        $minSubPrice = isset($product['min_sub_price']) ? (float) $product['min_sub_price'] : null;
        if (! empty($product['has_sub_products']) && $minSubPrice !== null) {
            return $minSubPrice;
        }

        return isset($product['price']) ? (float) $product['price'] : 0.0;
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

        return \Carbon\Carbon::parse($value)->timestamp;
    }

    private function resolveStockSettings(array $product)
    {
        $stock = $product['stock'] ?? null;
        $hasSubProducts = ! empty($product['has_sub_products']);

        if ($hasSubProducts && ! empty($product['sub_products'])) {
            $allZero = collect($product['sub_products'])->every(function ($subProduct) {
                return ($subProduct->stock ?? 0) === 0;
            });

            if ($allZero) {
                return 0;
            }
        }

        return $stock;
    }

    private function resolveSizeSectionName(array $product): ?string
    {
        if (! empty($product['has_sub_products'])) {
            return __('products::messages.size_section_name');
        }

        return null;
    }
}
