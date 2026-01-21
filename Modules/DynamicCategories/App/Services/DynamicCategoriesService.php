<?php

namespace Modules\DynamicCategories\App\Services;

use Modules\DynamicCategories\App\Services\Builders\SectionBuilder;
use Modules\DynamicCategories\App\Services\Cache\CacheService;

class DynamicCategoriesService
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
     * Get complete dynamic categories data
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getDynamicCategoriesData($request): array
    {
        $categoryId = $request->input('category_id', 0);
        if (!$categoryId) {
            return ['sections' => []];
        }

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
            $this->cacheService->clearDynamicCategoriesCache(categoryId: $categoryId, emirateId: $emirateId, regionId: $regionId, lang: $lang);
            $cachedData = $this->cacheService->getDynamicCategoriesData(categoryId: $categoryId, emirateId: $emirateId, regionId: $regionId, lang: $lang);
            if ($cachedData) {
                return $cachedData;
            }
        }

        // Build data
        $data = [
            'sections' => $this->sectionBuilder->buildAll($categoryId),
        ];

        // Store in cache
        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->storeDynamicCategoriesData(categoryId: $categoryId, emirateId: $emirateId, regionId: $regionId, data: $data, lang: $lang);
        }

        return $data;
    }
}

