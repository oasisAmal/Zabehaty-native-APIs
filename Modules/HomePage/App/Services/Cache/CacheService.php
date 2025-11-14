<?php

namespace Modules\HomePage\App\Services\Cache;

use App\Enums\AppCountries;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class CacheService
{
    protected int $defaultTtl = 300; // 300 seconds = 5 minutes

    /**
     * Get cache key for homepage data
     *
     * @param string $countryCode
     * @param string $lang
     * @return string
     */
    public function getHomePageCacheKey(string $countryCode, string $lang = 'en'): string
    {
        return "homepage:country:{$countryCode}:lang:{$lang}";
    }

    /**
     * Get homepage data from cache
     *
     * @param string $countryCode
     * @param string $lang
     * @return mixed
     */
    public function getHomePageData(string $countryCode, string $lang = 'en')
    {
        $key = $this->getHomePageCacheKey($countryCode, $lang);
        return Cache::get($key);
    }

    /**
     * Store homepage data in cache
     *
     * @param string $countryCode
     * @param array $data
     * @param string $lang
     * @param int|null $ttl
     * @return void
     */
    public function storeHomePageData(string $countryCode, array $data, string $lang = 'en', ?int $ttl = null): void
    {
        $key = $this->getHomePageCacheKey($countryCode, $lang);
        $ttl = $ttl ?? $this->defaultTtl;
        
        Cache::put($key, $data, $ttl);
    }

    /**
     * Clear homepage cache for specific country
     *
     * @param string $countryCode
     * @param string|null $lang
     * @return void
     */
    public function clearHomePageCache(string $countryCode, ?string $lang = null): void
    {
        if ($lang) {
            Cache::forget($this->getHomePageCacheKey($countryCode, $lang));
        } else {
            // Clear for all languages
            $languages = ['en', 'ar'];
            foreach ($languages as $lang) {
                Cache::forget($this->getHomePageCacheKey($countryCode, $lang));
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
        $countries = AppCountries::getValues();
        $languages = ['en', 'ar'];

        foreach ($countries as $country) {
            $country = strtolower($country);
            foreach ($languages as $lang) {
                Cache::forget($this->getHomePageCacheKey($country, $lang));
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
        return config('cache.default') !== 'null' && !config('app.env') === 'local';
        // return config('cache.default') !== 'null';
    }
}
