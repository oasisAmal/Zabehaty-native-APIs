<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use App\Enums\CountryCurrencies;
use Illuminate\Support\Facades\DB;
use Modules\Shops\App\Models\Shop;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Shops\App\Transformers\ShopCardResource;

class ProductDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brief' => $this->brief,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'images' => $this->images,
            'shop' => $this->shop?->name,
            'category' => $this->category?->name,
            'currency' => CountryCurrencies::getCurrency(),
            'price' => (float) $this->price,
            'price_before_discount' => (float) $this->old_price ?: null,
            'discount_percentage' => (float) $this->discount_percentage ?: null,
            'limited_offer_expired_at' => $this->limited_offer_expired_at ? $this->limited_offer_expired_at->timestamp : null,
            'badge' => $this->badge_name ?? null,
            'is_favorite' => (bool) $this->is_favorite,
            'has_gift' => $this->has_gift,
            'allow_gift' => $this->allow_gift,
            'allow_donate' => $this->allow_donate,
            'has_quantity' => $this->has_quantity,
            'quantity_settings' => $this->quantity_settings,
            'stock' => $this->stock_settings,
            'sizes' => $this->getSizes(),
            'available_shops' => $this->availableShops(),
            'available_restaurants' => $this->availableRestaurants(),
            'addon_sections' => AddonSectionResource::collection($this->addonSectionPivots ?? []),
        ];
    }

    private function getSizes()
    {
        if (!$this->has_sub_products) return [];
        return SubProductResource::collection($this->subProducts);
    }

    private function availableShops()
    {
        if (!$this->department) return null;
        $shops = $this->department->shops()
            ->leftJoin('shop_products', function ($join) {
                $join->on('shop_products.shop_id', '=', 'shops.id')
                    ->on('shop_products.product_id', '=', DB::raw($this->id));
            })
            ->where('type', 'shop')
            ->orderBy('shop_products.price')
            ->get();
        if ($shops->isEmpty() || $this->shop) return null;

        return [
            'section_name' => $this->department->shop_section_name,
            'shops' => ShopCardResource::collection($shops),
        ];
    }
    private function availableRestaurants()
    {
        if (!$this->department) return null;
        $restaurants = Shop::where('type', 'restaurant')
            ->whereHas('shopCookings', function ($q) {
                $q->where('product_id', $this->id);
            })
            ->get();
        if ($this->has_cookings == '0' || $this->productCookings->isEmpty() || $restaurants->isEmpty()) return null;

        return ShopCardResource::collection($restaurants);
    }
}
