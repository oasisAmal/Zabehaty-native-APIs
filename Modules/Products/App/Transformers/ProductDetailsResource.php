<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use App\Enums\CountryCurrencies;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Products\App\Services\ProductDetailsTransformerService;

class ProductDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $service = app(ProductDetailsTransformerService::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description_title' => $this->brief,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'images' => $service->getProductImages($this->resource),
            'shop' => $this->shop?->name,
            'category' => $this->category?->name,
            'currency' => CountryCurrencies::getCurrency(),
            'price' => $service->getProductPrice($this->resource),
            'price_before_discount' => (float) $this->old_price ?: null,
            'discount_percentage' => (float) $this->discount_percentage ?: null,
            'limited_offer_expired_at' => $this->limited_offer_expired_at ? $this->limited_offer_expired_at->timestamp : null,
            'badge' => $this->badge_name ?? null,
            'is_favorite' => (bool) $this->is_favorite,
            // 'has_gift' => $this->has_gift,
            // 'allow_gift' => $this->allow_gift,
            // 'allow_donate' => $this->allow_donate,
            'has_quantity' => $this->has_quantity,
            'quantity_settings' => $this->quantity_settings,
            'stock' => $this->stock_settings,
            'sizes' => $service->getSizes($this->resource),
            // 'available_shops' => $service->getAvailableShops($this->resource),
            // 'available_restaurants' => $service->getAvailableRestaurants($this->resource),
            'addon_sections' => AddonSectionResource::collection($this->addonSectionPivots ?? []),
        ];
    }
}
