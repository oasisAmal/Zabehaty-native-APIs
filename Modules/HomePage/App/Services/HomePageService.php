<?php

namespace Modules\HomePage\App\Services;

use Modules\HomePage\App\Services\Builders\HeaderBuilder;
use Modules\HomePage\App\Services\Builders\SectionBuilder;
use Modules\HomePage\App\Services\Cache\CacheService;

class HomePageService
{
    protected HeaderBuilder $headerBuilder;
    protected SectionBuilder $sectionBuilder;
    protected CacheService $cacheService;

    public function __construct(
        HeaderBuilder $headerBuilder,
        SectionBuilder $sectionBuilder,
        CacheService $cacheService
    ) {
        $this->headerBuilder = $headerBuilder;
        $this->sectionBuilder = $sectionBuilder;
        $this->cacheService = $cacheService;
    }

    /**
     * Get complete homepage data
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getHomePageData($request): array
    {
        $countryCode = strtolower($request->get('app_country_code', 'ae'));
        $lang = app()->getLocale();

        // Check cache first
        if ($this->cacheService->isCacheEnabled()) {
            $cachedData = $this->cacheService->getHomePageData($countryCode, $lang);
            if ($cachedData) {
                return $cachedData;
            }
        }

        // Build data
        $data = [
            'header' => $this->headerBuilder->build(),
            // 'sections' => $this->sectionBuilder->buildAll(), // until we have sections in the database
        ];

        // Store in cache
        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->storeHomePageData($countryCode, $data, $lang);
        }

        return $data;
    }
}
