<?php

namespace Modules\Categories\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Categories\App\Http\Requests\CategoryIndexRequest;
use Modules\Categories\App\Services\CategoriesService;
use Modules\Categories\App\Transformers\CategoryCardResource;

class CategoryController extends Controller
{
    protected CategoriesService $categoriesService;

    public function __construct(CategoriesService $categoriesService)
    {
        $this->categoriesService = $categoriesService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CategoryIndexRequest $request)
    {
        try {
            $categories = $this->categoriesService->getCategories($request->validated());
            return responseSuccessData(CategoryCardResource::collection($categories));
        } catch (\Exception $e) {
            return responseErrorMessage(
                __('categories::messages.failed_to_retrieve_categories'),
                500
            );
        }
    }
}
