<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Products\App\Services\AddonSectionItemTransformerService;

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
            'price' => app(AddonSectionItemTransformerService::class)->getPrice($this->resource),
        ];
    }
}

