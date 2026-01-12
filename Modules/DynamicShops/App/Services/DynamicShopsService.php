<?php

namespace Modules\DynamicShops\App\Services;

use Modules\DynamicShops\App\Services\Builders\SectionBuilder;
use Modules\DynamicShops\App\Services\Cache\CacheService;

class DynamicShopsService
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
     * Get complete dynamic shops data
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getDynamicShopsData($request): array
    {
        $shopId = $request->input('shop_id', 0);
        
        if (!$shopId) {
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
            $this->cacheService->clearDynamicShopsCache(shopId: $shopId, emirateId: $emirateId, regionId: $regionId, lang: $lang);
            $cachedData = $this->cacheService->getDynamicShopsData(shopId: $shopId, emirateId: $emirateId, regionId: $regionId, lang: $lang);
            if ($cachedData) {
                return $cachedData;
            }
        }

        // Build data
        $data = [
            'sections' => $this->sectionBuilder->buildAll($shopId),
        ];

        // Store in cache
        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->storeDynamicShopsData(shopId: $shopId, emirateId: $emirateId, regionId: $regionId, data: $data, lang: $lang);
        }

        return $data;
    }
}
