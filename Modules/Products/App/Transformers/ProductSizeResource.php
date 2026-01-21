<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Products\App\Services\ProductSizeTransformerService;

class ProductSizeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $service = app(ProductSizeTransformerService::class);
        $categoryId = $this->category_id ?? null;
        if ($categoryId !== null) {
            $service->setCategoryId($categoryId);
        }

        return [
            'id' => $this->id,
            'name' => $service->getName($this->resource),
            'image' => $this->image,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'notes' => $this->notes,
            'weight' => $service->getWeight($this->resource),
            'age' => $service->getAge($this->resource),
            'enough_for_from' => $this->enough_for_from,
            'enough_for_to' => $this->enough_for_to,
            'stock' => $this->stock,
        ];
    }
}
