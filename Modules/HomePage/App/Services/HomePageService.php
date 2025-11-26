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
        $emirateId = 0;
        $regionId = 0;
        $defaultAddress = auth('api')->user()->defaultAddress;
        if ($defaultAddress) {
            $emirateId = $defaultAddress->emirate_id;
            $regionId = $defaultAddress->region_id;
        }
        $lang = app()->getLocale();

        // Check cache first
        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->clearHomePageCache(emirateId: $emirateId, regionId: $regionId, lang: $lang);
            $cachedData = $this->cacheService->getHomePageData(emirateId: $emirateId, regionId: $regionId, lang: $lang);
            if ($cachedData) {
                return $cachedData;
            }
        }

        // Build data
        $data = [
            'header' => $this->headerBuilder->build(),
            'sections' => $this->sectionBuilder->buildAll(),
        ];

        // Store in cache
        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->storeHomePageData(emirateId: $emirateId, regionId: $regionId, data: $data, lang: $lang);
        }

        return $data;
    }
}
