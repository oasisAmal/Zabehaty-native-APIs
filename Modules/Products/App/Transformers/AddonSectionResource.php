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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'is_required' => (bool) ($this->pivot->is_required ?? false),
            'items' => AddonSectionItemResource::collection($this->pivot->itemsPivots ?? collect()),
        ];
    }
}

