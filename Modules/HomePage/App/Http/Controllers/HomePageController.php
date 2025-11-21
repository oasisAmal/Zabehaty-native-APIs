<?php

namespace Modules\HomePage\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HomePage\App\Services\HomePageService;
use Modules\HomePage\App\Transformers\HomePageResource;

class HomePageController extends Controller
{
    protected HomePageService $homePageService;

    public function __construct(HomePageService $homePageService)
    {
        $this->homePageService = $homePageService;
    }

    /**
     * Get homepage data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $homePageData = $this->homePageService->getHomePageData($request);
            return responseSuccessData(HomePageResource::make($homePageData));
        } catch (\Exception $e) {
            dd($e->getMessage());
            return responseErrorMessage(
                __('homepage::messages.failed_to_retrieve_homepage_data'),
                500
            );
        }
    }
}
