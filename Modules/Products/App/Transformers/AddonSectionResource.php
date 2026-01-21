<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddonSectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $items = $this->items ?? [];

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'is_required' => (bool) ($this->is_required ?? false),
            'items' => collect($items)->map(fn ($item) => new AddonSectionItemResource((object) $item)),
        ];
    }
}

