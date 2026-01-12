<?php

namespace Modules\DynamicShops\App\Services\Cache;

use App\Models\Emirate;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    protected int $defaultTtl = 0;

    public function __construct()
    {
        $this->defaultTtl = config('dynamicshops.cache.default_ttl');
    }

    /**
     * Get cache key for dynamic shops data
     *
     * @param int $shopId
     * @param int $emirateId
     * @param int $regionId
     * @param string $lang
     * @return string
     */
    public function getDynamicShopsCacheKey(int $shopId, int $emirateId, int $regionId, string $lang = 'en'): string
    {
        return "dynamic_shops:shop_id:{$shopId}:emirate_id:{$emirateId}:region_id:{$regionId}:lang:{$lang}";
    }

    /**
     * Get dynamic shops data from cache
     *
     * @param int $shopId
     * @param int $emirateId
     * @param int $regionId
     * @param string $lang
     * @return mixed
     */
    public function getDynamicShopsData(int $shopId, int $emirateId, int $regionId, string $lang = 'en')
    {
        $key = $this->getDynamicShopsCacheKey($shopId, $emirateId, $regionId, $lang);
        return Cache::get($key);
    }

    /**
     * Store dynamic shops data in cache
     *
     * @param int $shopId
     * @param int $emirateId
     * @param int $regionId
     * @param array $data
     * @param string $lang
     * @param int|null $ttl
     * @return void
     */
    public function storeDynamicShopsData(int $shopId, int $emirateId = 0, int $regionId = 0, array $data, string $lang = 'en', ?int $ttl = null): void
    {
        $key = $this->getDynamicShopsCacheKey(shopId: $shopId, emirateId: $emirateId, regionId: $regionId, lang: $lang);
        $ttl = $ttl ?? $this->defaultTtl;
        
        Cache::put($key, $data, ttl: $ttl);
    }

    /**
     * Clear dynamic shops cache for specific shop
     *
     * @param int $shopId
     * @param int $emirateId
     * @param int $regionId
     * @param string|null $lang
     * @return void
     */
    public function clearDynamicShopsCache(int $shopId, int $emirateId = 0, int $regionId = 0, ?string $lang = null): void
    {
        if ($lang) {
            Cache::forget($this->getDynamicShopsCacheKey(shopId: $shopId, emirateId: $emirateId, regionId: $regionId, lang: $lang));
        } else {
            // Clear for all languages
            $languages = ['en', 'ar'];
            foreach ($languages as $lang) {
                Cache::forget($this->getDynamicShopsCacheKey(shopId: $shopId, emirateId: $emirateId, regionId: $regionId, lang: $lang));
            }
        }
    }

    /**
     * Clear all dynamic shops cache
     *
     * @return void
     */
    public function clearAllDynamicShopsCache(): void
    {
        $emirateIds = Emirate::all()->pluck('id')->toArray();
        $languages = ['en', 'ar'];

        foreach ($emirateIds as $emirateId) {
            foreach ($languages as $lang) {
                Cache::forget($this->getDynamicShopsCacheKey(shopId: 0, emirateId: $emirateId, regionId: 0, lang: $lang));
            }
        }
    }

    /**
     * Check if cache is enabled
     *
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return config('dynamicshops.cache.enabled');
    }
}
