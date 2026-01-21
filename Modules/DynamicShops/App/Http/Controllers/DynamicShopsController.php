<?php

namespace Modules\DynamicShops\App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\DynamicShops\App\Services\DynamicShopsService;
use Modules\DynamicShops\App\Transformers\DynamicShopsResource;
use Modules\DynamicShops\App\Http\Requests\DynamicShopsIndexRequest;

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
            dd($e->getMessage());
            Log::error('failed_to_retrieve_dynamic_shops_data', ['message' => $e->getMessage()]);
            return responseErrorMessage(
                __('dynamicshops::messages.failed_to_retrieve_dynamic_shops_data'),
                500
            );
        }
    }
}
