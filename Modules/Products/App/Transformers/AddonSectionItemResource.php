<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Products\App\Services\AddonSectionItemTransformerService;

class AddonSectionItemResource extends JsonResource
{
    /**
     * Cached service instance
     */
    private static ?AddonSectionItemTransformerService $service = null;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        if (self::$service === null) {
            self::$service = app(AddonSectionItemTransformerService::class);
        }

        return [
            'id' => $this->product_addon_section_item_id,
            'title' => $this->title,
            'price' => self::$service->getPrice($this->resource),
            'image_url' => $this->image,
            'media' => self::$service->getMedia($this->resource),
        ];
    }
}

