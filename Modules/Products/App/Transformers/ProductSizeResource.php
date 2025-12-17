<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Products\App\Services\ProductSizeTransformerService;

class ProductSizeResource extends JsonResource
{
    /**
     * Cached service instance
     */
    private static ?ProductSizeTransformerService $service = null;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        if (self::$service === null) {
            self::$service = app(ProductSizeTransformerService::class);
        }

        return [
            'id' => $this->id,
            'name' => self::$service->getName($this->resource),
            'descripion' => $this->descripion,
            'image' => $this->image,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'notes' => $this->notes,
            'weight' => self::$service->getWeight($this->resource),
            'age' => self::$service->getAge($this->resource),
            'enough_for_from' => $this->enough_for_from,
            'enough_for_to' => $this->enough_for_to,
            'stock' => $this->stock,
        ];
    }
}
