<?php

namespace Modules\DynamicShops\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DynamicShops\App\Http\Requests\DynamicShopsIndexRequest;
use Modules\DynamicShops\App\Services\DynamicShopsService;
use Modules\DynamicShops\App\Transformers\DynamicShopsResource;

class DynamicShopsController extends Controller
{
    protected DynamicShopsService $dynamicShopsService;

    public function __construct(DynamicShopsService $dynamicShopsService)
    {
        $this->dynamicShopsService = $dynamicShopsService;
    }

    /**
     * Get dynamic shops data
     *
     * @param DynamicShopsIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(DynamicShopsIndexRequest $request)
    {
        try {
            $dynamicShopsData = $this->dynamicShopsService->getDynamicShopsData($request);
            return responseSuccessData(DynamicShopsResource::make($dynamicShopsData));
        } catch (\Exception $e) {
            return responseErrorMessage(
                __('dynamicshops::messages.failed_to_retrieve_dynamic_shops_data'),
                500
            );
        }
    }
}
