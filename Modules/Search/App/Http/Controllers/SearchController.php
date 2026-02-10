<?php

namespace Modules\Search\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Search\App\Http\Requests\SuggestionsRequest;
use Modules\Search\App\Services\SearchService;
use Modules\Search\App\Transformers\SearchResource;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

   /**
    * Prepare search data
    *
    * @param Request $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function prepare(Request $request)
    {
        try {
            $searchData = $this->searchService->getSearchData();
            return responseSuccessData(SearchResource::make($searchData));
        } catch (\Exception $e) {
            Log::error('Search prepare failed: '.$e->getMessage(), ['exception' => $e]);

            return responseErrorMessage(
                __('search::messages.failed_to_prepare_search_data'),
                500
            );
        }
    }

    /**
     * Get search suggestions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(SuggestionsRequest $request)
    {
        try {
            $suggestions = $this->searchService->getSearchSuggestions($request->validated());
            return responseSuccessData($suggestions);
        } catch (\Exception $e) {
            Log::error('Search suggestions failed: '.$e->getMessage(), ['exception' => $e]);

            return responseErrorMessage(
                __('search::messages.failed_to_get_search_suggestions'),
                500
            );
        }
    }
}
