<?php

namespace Modules\Cart\App\Services;

use Illuminate\Support\Facades\Log;
use Modules\Products\App\Models\Product;
use Modules\Cart\App\Services\Product\ProductPriceCalculator;

class CartService
{
    /**
     * Calculate the price of a product
     *
     * @param array $data
     * @return array
     */
    public function calculateProduct(array $data): array
    {
        // Get product
        $product = Product::withoutGlobalScopes()->find($data['product_id']);

        // Prepare data for price calculation
        $calculateData = [
            'product' => $product,
            'size_id' => $data['size_id'] ?? null,
            'quantity' => $data['quantity'] ?? 1,
            'addon_items' => $data['addon_items'] ?? [],
        ];

        Log::info('calculateData', $calculateData);

        // Calculate price using ProductPriceCalculator
        $calculator = new ProductPriceCalculator();
        $finalPrice = $calculator->calculate($calculateData);
        
        // Check stock availability using modifier
        // $canBeAddedToCart = $calculator->canBeAddedToCart($calculateData);
        
        return [
            'price' => round($finalPrice, 2),
            // 'can_be_added_to_cart' => $canBeAddedToCart,
        ];
    }
}
