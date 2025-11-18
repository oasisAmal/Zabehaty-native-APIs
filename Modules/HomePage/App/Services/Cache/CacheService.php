<?php

namespace Modules\HomePage\App\Services\Cache;

use App\Models\Emirate;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    protected int $defaultTtl = 0;

    public function __construct()
    {
        $this->defaultTtl = config('homepage.cache.default_ttl');
    }

    /**
     * Get cache key for homepage data
     *
     * @param int $emirateId
     * @param int $regionId
     * @param string $lang
     * @return string
     */
    public function getHomePageCacheKey(int $emirateId, int $regionId, string $lang = 'en'): string
    {
        return "homepage:emirate_id:{$emirateId}:region_id:{$regionId}:lang:{$lang}";
    }

    /**
     * Get homepage data from cache
     *
     * @param int $emirateId
     * @param int $regionId
     * @param string $lang
     * @return mixed
     */
    public function getHomePageData(int $emirateId, int $regionId, string $lang = 'en')
    {
        $key = $this->getHomePageCacheKey($emirateId, $regionId, $lang);
        return Cache::get($key);
    }

    /**
     * Store homepage data in cache
     *
     * @param int $emirateId
     * @param int $regionId
     * @param array $data
     * @param string $lang
     * @param int|null $ttl
     * @return void
     */
    public function storeHomePageData(int $emirateId = 0, int $regionId = 0, array $data, string $lang = 'en', ?int $ttl = null): void
    {
        $key = $this->getHomePageCacheKey(emirateId:$emirateId, regionId:$regionId, lang:$lang);
        $ttl = $ttl ?? $this->defaultTtl;
        
        Cache::put($key, $data, ttl: $ttl);
    }

    /**
     * Clear homepage cache for specific country
     *
     * @param int $emirateId
     * @param int $regionId
     * @param string|null $lang
     * @return void
     */
    public function clearHomePageCache(int $emirateId = 0, int $regionId = 0, ?string $lang = null): void
    {
        if ($lang) {
            Cache::forget($this->getHomePageCacheKey(emirateId: $emirateId, regionId: $regionId, lang: $lang));
        } else {
            // Clear for all languages
            $languages = ['en', 'ar'];
            foreach ($languages as $lang) {
                Cache::forget($this->getHomePageCacheKey(emirateId: $emirateId, regionId: $regionId, lang: $lang));
            }
        }
    }

    /**
     * Clear all homepage cache
     *
     * @return void
     */
    public function clearAllHomePageCache(): void
    {
        $emirateIds = Emirate::all()->pluck('id')->toArray();
        $languages = ['en', 'ar'];

        foreach ($emirateIds as $emirateId) {
            foreach ($languages as $lang) {
                Cache::forget($this->getHomePageCacheKey(emirateId: $emirateId, regionId: 0, lang: $lang));
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
        return config('homepage.cache.enabled');
    }
}
