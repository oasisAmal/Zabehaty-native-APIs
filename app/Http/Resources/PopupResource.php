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
            'target_page' => $this->target_page,
            'size' => $this->size,
            'media_type' => $this->media_type,
            'media_url' => $this->media_url,
            'thumbnail_url' => $this->thumbnail_url ?? '',
            'external_link' => $this->link ?? '',
            'item_id' => $this->item_id ?? 0,
            'item_type' => $this->item_type ?? '',
        ];
    }
}
