<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddonSectionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->product_addon_section_item_id,
            'title' => $this->title,
            'price' => $this->getPrice(),
        ];
    }

    private function getPrice()
    {
        $price = null;
        if (isset($this->pivot->price) && $this->pivot->price) {
            $price = (float) $this->pivot->price;
        } elseif (isset($this->price) && $this->price) {
            $price = (float) $this->price;
        }
        return $price ?? null;
    }
}

