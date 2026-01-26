<?php

namespace Modules\Shops\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Shops\App\Http\Requests\ShopIndexRequest;
use Modules\Shops\App\Services\ShopsService;
use Modules\Shops\App\Transformers\ShopCardResource;

class ShopController extends Controller
{
    protected ShopsService $shopsService;

    public function __construct(ShopsService $shopsService)
    {
        $this->shopsService = $shopsService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(ShopIndexRequest $request)
    {
        try {
            $shops = $this->shopsService->getShops($request->validated());
            return responsePaginate(ShopCardResource::collection($shops));
        } catch (\Exception $e) {
            dd($e->getMessage());
            return responseErrorMessage(
                __('shops::messages.failed_to_retrieve_shops'),
                500
            );
        }
    }
}
