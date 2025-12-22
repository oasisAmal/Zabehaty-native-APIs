<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use App\Enums\CountryCurrencies;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Products\App\Services\ProductDetailsTransformerService;

class ProductCardResource extends JsonResource
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
            'image_url' => $this->thumb,
            'shop' => $this->shop?->name,
            'category' => $this->category?->name,
            'currency' => CountryCurrencies::getCurrency(),
            'price' => $service->getProductPrice($this->resource),
            'price_before_discount' => (float) $this->old_price ?: null,
            'discount_percentage' => (float) $this->discount_percentage ?: null,
            'limited_offer_expired_at' => $this->limited_offer_expired_at ? $this->limited_offer_expired_at->timestamp : null,
            'badge' => $this->badge_name ?? null,
            'is_favorite' => (bool) $this->is_favorite,
        ];
    }
}
