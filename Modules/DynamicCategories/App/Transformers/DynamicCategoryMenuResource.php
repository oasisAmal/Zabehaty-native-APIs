<?php

namespace Modules\DynamicCategories\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicCategoryMenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->menu_item_parent_id,
            'title' => $this->title,
            'image_url' => $this->getImageUrl(),
        ];
    }

    public function getImageUrl(): string
    {
        if (request()->app_lang == 'ar') {
            return $this->image_ar_url ?? '';
        }
        return $this->image_en_url ?? '';
    }
}
