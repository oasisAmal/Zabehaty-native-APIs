<?php

namespace Modules\Search\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'recent_search_words' => $this->resource['recent_search_words'],
            'banners' => $this->resource['banners'],
            'recently_viewed_products' => $this->resource['recently_viewed_products'],
        ];
    }
}
