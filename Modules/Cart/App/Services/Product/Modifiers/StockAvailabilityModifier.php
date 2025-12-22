<?php

namespace Modules\Cart\App\Services\Product\Modifiers;

use Modules\Products\App\Models\SubProduct;

class StockAvailabilityModifier
{
    /**
     * Check if product can be added to cart based on stock
     *
     * @param array $data Contains: 'product' => Product, 'size_id' => int|null
     * @return bool
     */
    public function canBeAddedToCart(array $data): bool
    {
        $product = $data['product'] ?? null;
        $sizeId = $data['size_id'] ?? null;

        if (!$product) {
            return false;
        }

        // If product has subproducts and size_id is provided, check SubProduct stock
        if ($product->has_sub_products && $sizeId) {
            $subProduct = SubProduct::where('id', $sizeId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();

            if ($subProduct) {
                return (int) $subProduct->stock > 0;
            }

            // If subproduct not found, return false
            return false;
        }

        // Otherwise, check Product stock
        return (int) $product->stock > 0;
    }
}

