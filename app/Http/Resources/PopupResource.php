<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopupResource extends JsonResource
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
            'target_page' => $this->target_page,
            'size' => $this->size,
            'image_url' => request()->app_lang == 'ar' ? $this->image_ar_url : $this->image_en_url,
            'thumbnail_url' => $this->thumbnail_url,
            'video_url' => $this->video_url,
            'link' => $this->link,
            'item_data' => $this->item_data,
            'item_type' => $this->item_type
        ];
    }
}
