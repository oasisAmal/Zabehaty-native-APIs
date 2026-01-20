<?php

namespace Modules\DynamicCategories\App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\DynamicCategories\App\Services\DynamicCategoriesService;
use Modules\DynamicCategories\App\Transformers\DynamicCategoriesResource;
use Modules\DynamicCategories\App\Http\Requests\DynamicCategoriesIndexRequest;

class DynamicCategoriesController extends Controller
{
    protected DynamicCategoriesService $dynamicCategoriesService;

    public function __construct(DynamicCategoriesService $dynamicCategoriesService)
    {
        $this->dynamicCategoriesService = $dynamicCategoriesService;
    }

    /**
     * Get dynamic categories data
     *
     * @param DynamicCategoriesIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(DynamicCategoriesIndexRequest $request)
    {
        try {
            $dynamicCategoriesData = $this->dynamicCategoriesService->getDynamicCategoriesData($request);
            return responseSuccessData(DynamicCategoriesResource::make($dynamicCategoriesData));
        } catch (\Exception $e) {
            Log::error('failed_to_retrieve_dynamic_categories_data', ['message' => $e->getMessage()]);
            return responseErrorMessage(
                __('dynamiccategories::messages.failed_to_retrieve_dynamic_categories_data'),
                500
            );
        }
    }
}
