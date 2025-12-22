<?php

namespace Modules\Cart\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Cart\App\Services\CartService;
use Modules\Cart\App\Http\Requests\CalculateProductRequest;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function calculateProduct(CalculateProductRequest $request)
    {
        try {
            $result = $this->cartService->calculateProduct($request->validated());
            return responseSuccessData($result);
        } catch (\Exception $e) {
            return responseErrorMessage(
                __('cart::messages.failed_to_calculate_product'),
                500
            );
        }
    }
}
