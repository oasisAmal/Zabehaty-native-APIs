<?php

namespace Modules\Products\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Products\App\Models\Product;
use Modules\Shops\App\Models\Shop;
use Modules\Products\App\Transformers\ProductSizeResource;
use Modules\Shops\App\Transformers\ShopCardResource;

class ProductDetailsTransformerService
{
    /**
     * Get product sizes
     *
     * @param Product $product
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|array
     */
    public function getSizes(Product $product)
    {
        if (!$product->has_sub_products) {
            return [];
        }

        return ProductSizeResource::collection($product->subProducts);
    }

    /**
     * Get available shops for product
     *
     * @param Product $product
     * @return array|null
     */
    public function getAvailableShops(Product $product): ?array
    {
        if (!$product->department) {
            return null;
        }

        $shops = $product->department->shops()
            ->leftJoin('shop_products', function ($join) use ($product) {
                $join->on('shop_products.shop_id', '=', 'shops.id')
                    ->on('shop_products.product_id', '=', DB::raw($product->id));
            })
            ->where('type', 'shop')
            ->orderBy('shop_products.price')
            ->get();

        if ($shops->isEmpty() || $product->shop) {
            return null;
        }

        return [
            'section_name' => $product->department->shop_section_name,
            'shops' => ShopCardResource::collection($shops),
        ];
    }

    /**
     * Get available restaurants for product
     *
     * @param Product $product
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|null
     */
    public function getAvailableRestaurants(Product $product)
    {
        if (!$product->department) {
            return null;
        }

        $restaurants = Shop::where('type', 'restaurant')
            ->whereHas('shopCookings', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->get();

        if ($product->has_cookings == '0' || $product->productCookings->isEmpty() || $restaurants->isEmpty()) {
            return null;
        }

        return ShopCardResource::collection($restaurants);
    }

    /**
     * Get product price
     *
     * @param Product $product
     * @return float
     */
    public function getProductPrice(Product $product)
    {
        if ($product->has_sub_products) {
            return (float) $product->subProducts->min('price') ?? 0;
        }

        return (float) $product->price;
    }

    /**
     * Get product images array
     *
     * @param Product $product
     * @return array
     */
    public function getProductImages(Product $product)
    {
        return (array) ($product->images == "" || $product->images == null ? [] : (array) $product->images);
    }
}

