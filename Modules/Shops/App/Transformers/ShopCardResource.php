<?php

namespace Modules\Shops\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $paymentBadges = $this->payment_badges ?? ['tamara', 'tabby'];

        return [
            'id' => $this->id,
            'name' => $this->name,
            // 'image_url' => $this->homesection_image ?? '',
            'image_url' => $this->banner ?? '',
            'logo_url' => $this->image ?? '',
            'rating' => $this->rating ? (float) $this->rating : null,
            'category' => $this->first_parent_category?->name ?? ($this->first_parent_category_name ?? ''),
            'payment_badges' => $paymentBadges,
        ];
    }
}
