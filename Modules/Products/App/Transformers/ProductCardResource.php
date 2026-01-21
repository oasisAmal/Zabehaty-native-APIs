<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Enums\CountryCurrencies;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_url' => $this->image_url ?? ($this->image ?? null),
            'shop' => $this->shop_name ?? null,
            'category' => $this->category_name ?? null,
            'currency' => CountryCurrencies::getCurrency(),
            'price' => $this->resolvePrice(),
            'price_before_discount' => (float) $this->old_price ?: null,
            'discount_percentage' => (float) ($this->discount_percentage ?? null) ?: null,
            'limited_offer_expired_at' => $this->resolveLimitedOfferTimestamp($this->limited_offer_expired_at),
            'badge' => $this->badge_name ?? null,
            'is_favorite' => (bool) ($this->is_favorite ?? false),
        ];
    }

    private function resolvePrice(): float
    {
        if ($this->has_sub_products && $this->min_sub_price !== null) {
            return (float) $this->min_sub_price;
        }

        return (float) ($this->price ?? 0);
    }

    private function resolveLimitedOfferTimestamp($value): ?int
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
}
