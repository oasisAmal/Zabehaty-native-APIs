<?php

namespace Modules\Products\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Products\App\Services\ProductsService;
use Modules\Products\App\Transformers\ProductCardResource;
use Modules\Products\App\Http\Requests\ProductIndexRequest;
use Modules\Products\App\Transformers\ProductDetailsResource;

class ProductController extends Controller
{
    protected ProductsService $productsService;

    public function __construct(ProductsService $productsService)
    {
        $this->productsService = $productsService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(ProductIndexRequest $request)
    {
        try {
            $products = $this->productsService->getProducts($request->validated());
            return responsePaginate(ProductCardResource::collection($products));
        } catch (\Exception $e) {
            return responseErrorMessage(
                __('products::messages.failed_to_retrieve_products'),
                500
            );
        }
    }

    /**
     * Get product details
     */
    public function details($id)
    {
        try {
            $product = $this->productsService->getProductDetails($id);
            if (!$product) {
                return responseErrorMessage(
                    __('products::messages.product_not_found'),
                    404
                );
            }
            return responseSuccessData(ProductDetailsResource::make($product));
        } catch (\Exception $e) {
            return responseErrorMessage(
                __('products::messages.failed_to_retrieve_product_details'),
                500,
                $e->getMessage()
            );
        }
    }   
}
