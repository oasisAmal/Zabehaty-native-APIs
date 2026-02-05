<?php

namespace Modules\Search\App\Services;

use Modules\Search\App\Services\Builders\SectionBuilder;
use Modules\Search\App\Services\Cache\CacheService;
use Modules\Search\Enums\SearchSectionType;

class SearchService
{
    protected SectionBuilder $sectionBuilder;

    protected CacheService $cacheService;

    public function __construct(
        SectionBuilder $sectionBuilder,
        CacheService $cacheService
    ) {
        $this->sectionBuilder = $sectionBuilder;
        $this->cacheService = $cacheService;
    }

    /**
     * Prepare search data
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getSearchData($request): array
    {
        $emirateId = 0;
        $regionId = 0;
        $defaultAddress = auth('api')->user()->defaultAddress;
        if ($defaultAddress) {
            $emirateId = $defaultAddress->emirate_id;
            $regionId = $defaultAddress->region_id;
        }
        $lang = app()->getLocale();

        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->clearSearchCache(emirateId: $emirateId, regionId: $regionId, lang: $lang);
            $cachedData = $this->cacheService->getSearchData(emirateId: $emirateId, regionId: $regionId, lang: $lang);
            if ($cachedData) {
                return $cachedData;
            }
        }

        $data = [
            'recent_search_words' => $this->sectionBuilder->buildSection(SearchSectionType::RECENT_SEARCH_WORDS),
            'banners' => $this->sectionBuilder->buildSection(SearchSectionType::BANNERS),
            'recently_viewed_products' => $this->sectionBuilder->buildSection(SearchSectionType::RECENTLY_VIEWED_PRODUCTS),
        ];

        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->storeSearchData(emirateId: $emirateId, regionId: $regionId, data: $data, lang: $lang);
        }

        return $data;
    }
}
