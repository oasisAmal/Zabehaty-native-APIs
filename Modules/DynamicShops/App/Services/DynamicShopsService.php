<?php

namespace Modules\DynamicShops\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Shops\App\Models\Shop;
use Modules\Shops\App\Transformers\ShopCardResource;
use Modules\DynamicShops\App\Services\Cache\CacheService;
use Modules\DynamicShops\App\Services\Builders\SectionBuilder;

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
        $countryCode = strtolower((string) $request->get('app_country_code'));
        $shop = DB::connection($countryCode)->table('shops')->where('id', $shopId)->first();
        if (!$shop) {
            return [
                'shop' => null,
                'sections' => [],
            ];
        }
        saveUserVisit(shopId: $shopId);

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
            'shop' => ShopCardResource::make($shop),
            'sections' => $this->sectionBuilder->buildAll($shopId),
        ];

        // Store in cache
        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->storeDynamicShopsData(shopId: $shopId, emirateId: $emirateId, regionId: $regionId, data: $data, lang: $lang);
        }

        return $data;
    }
}
