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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_url' => $this->homesection_image,
            'logo_url' => $this->image,
            'rating' => $this->rating ?? null,
            'category' => $this->first_parent_category?->name ?? null,
            'payment_badges' => $this->payment_badges ?? [],
        ];
    }
}
