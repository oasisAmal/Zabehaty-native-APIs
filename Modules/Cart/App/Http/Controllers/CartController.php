<?php

namespace Modules\Cart\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Products\App\Models\Product;
use Modules\Cart\App\Services\ProductPriceCalculator;
use Modules\Cart\App\Http\Requests\CalculateProductRequest;

class CartController extends Controller
{
    public function calculateProduct(CalculateProductRequest $request)
    {
        try {
            // Get product
            $product = Product::withoutGlobalScopes()->find($request->product_id);

            // Prepare data for price calculation
            $data = [
                'product' => $product,
                'size_id' => $request->size_id ?? null,
                'quantity' => $request->quantity ?? 1,
                'addon_items' => $request->addon_items ?? [],
            ];

            // Calculate price using ProductPriceCalculator
            $calculator = new ProductPriceCalculator();
            $finalPrice = $calculator->calculate($data);

            return responseSuccessData([
                'price' => round($finalPrice, 2),
            ]);
        } catch (\Exception $e) {
            return responseErrorMessage(
                __('cart::messages.failed_to_calculate_product'),
                500,
                $e->getMessage()
            );
        }
    }
}
