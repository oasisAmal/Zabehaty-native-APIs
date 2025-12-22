<?php

namespace Modules\Cart\App\Services\PriceModifiers;

use Modules\Products\App\Models\Product;
use Modules\Products\App\Models\SubProduct;
use Modules\Cart\App\Interfaces\PriceModifierInterface;

class BasePriceModifier implements PriceModifierInterface
{
    /**
     * Calculate the base price from Product or SubProduct
     *
     * @param float $basePrice (ignored, always starts from 0)
     * @param array $data Contains: 'product' => Product, 'size_id' => int|null
     * @return float
     */
    public function calculate(float $basePrice, array $data): float
    {
        /** @var Product $product */
        $product = $data['product'] ?? null;

        if (!$product) {
            return 0.0;
        }

        // If size_id is provided, use SubProduct price (replacement)
        if (isset($data['size_id']) && $data['size_id']) {
            $subProduct = SubProduct::where('id', $data['size_id'])
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();

            if ($subProduct && $subProduct->price) {
                return (float) $subProduct->price;
            }
        }

        // Otherwise, use Product base price
        return (float) ($product->price ?? 0);
    }
}

