<?php

namespace Modules\Products\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Products\App\Http\Requests\ProductIndexRequest;
use Modules\Products\App\Services\ProductsService;
use Modules\Products\App\Transformers\ProductCardResource;

class ProductsController extends Controller
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
}
