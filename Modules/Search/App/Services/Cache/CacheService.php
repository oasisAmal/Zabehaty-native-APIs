<?php

namespace Modules\Search\App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    protected int $defaultTtl = 0;

    public function __construct()
    {
        $this->defaultTtl = config('search.cache.default_ttl');
    }

    /**
     * Get cache key for search data
     *
     * @param int $emirateId
     * @param int $regionId
     * @param string $lang
     * @return string
     */
    public function getSearchCacheKey(int $emirateId, int $regionId, string $lang = 'en'): string
    {
        return "search:emirate_id:{$emirateId}:region_id:{$regionId}:lang:{$lang}";
    }

    /**
     * Get search data from cache
     *
     * @param int $emirateId
     * @param int $regionId
     * @param string $lang
     * @return mixed
     */
    public function getSearchData(int $emirateId, int $regionId, string $lang = 'en')
    {
        $key = $this->getSearchCacheKey(emirateId: $emirateId, regionId: $regionId, lang: $lang);

        return Cache::get($key);
    }

    /**
     * Store search data in cache
     *
     * @param int $emirateId
     * @param int $regionId
     * @param array $data
     * @param string $lang
     * @param int|null $ttl
     * @return void
     */
    public function storeSearchData(int $emirateId, int $regionId, array $data, string $lang = 'en', ?int $ttl = null): void
    {
        $key = $this->getSearchCacheKey(emirateId: $emirateId, regionId: $regionId, lang: $lang);
        $ttl = $ttl ?? $this->defaultTtl;

        Cache::put($key, $data, $ttl);
    }

    /**
     * Clear search cache for user
     *
     * @param int $emirateId
     * @param int $regionId
     * @param string|null $lang
     * @return void
     */
    public function clearSearchCache(int $emirateId, int $regionId, ?string $lang = null): void
    {
        if ($lang) {
            Cache::forget($this->getSearchCacheKey(emirateId: $emirateId, regionId: $regionId, lang: $lang));
        } else {
            foreach (['en', 'ar'] as $lang) {
                Cache::forget($this->getSearchCacheKey(emirateId: $emirateId, regionId: $regionId, lang: $lang));
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
        return config('search.cache.enabled');
    }
}
