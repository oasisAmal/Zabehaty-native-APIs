<?php

namespace Modules\DynamicCategories\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicCategoryBannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->getImageUrl(),
            'item_type' => $this->getItemTypeName(),
            'item_id' => $this->item_id ?? 0,
            'external_link' => $this->external_link ?? '',
        ];
    }

    public function getImageUrl(): string
    {
        if (request()->app_lang == 'ar') {
            return $this->image_ar_url ?? '';
        }
        return $this->image_en_url ?? '';
    }

    public function getItemTypeName(): string
    {
        return match ($this->item_type) {
            'Modules\Products\App\Models\Product' => 'product',
            'Modules\Categories\App\Models\Category' => 'category',
            'Modules\Shops\App\Models\Shop' => 'shop',
            default => '',
        };
    }
}
