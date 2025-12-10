<?php

namespace Modules\DynamicCategories\App\Services\Cache;

use App\Models\Emirate;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    protected int $defaultTtl = 0;

    public function __construct()
    {
        $this->defaultTtl = config('dynamiccategories.cache.default_ttl');
    }

    /**
     * Get cache key for dynamic categories data
     *
     * @param int $categoryId
     * @param int $emirateId
     * @param int $regionId
     * @param string $lang
     * @return string
     */
    public function getDynamicCategoriesCacheKey(int $categoryId, int $emirateId, int $regionId, string $lang = 'en'): string
    {
        return "dynamic_categories:category_id:{$categoryId}:emirate_id:{$emirateId}:region_id:{$regionId}:lang:{$lang}";
    }

    /**
     * Get dynamic categories data from cache
     *
     * @param int $categoryId
     * @param int $emirateId
     * @param int $regionId
     * @param string $lang
     * @return mixed
     */
    public function getDynamicCategoriesData(int $categoryId, int $emirateId, int $regionId, string $lang = 'en')
    {
        $key = $this->getDynamicCategoriesCacheKey($categoryId, $emirateId, $regionId, $lang);
        return Cache::get($key);
    }

    /**
     * Store dynamic categories data in cache
     *
     * @param int $categoryId
     * @param int $emirateId
     * @param int $regionId
     * @param array $data
     * @param string $lang
     * @param int|null $ttl
     * @return void
     */
    public function storeDynamicCategoriesData(int $categoryId, int $emirateId = 0, int $regionId = 0, array $data, string $lang = 'en', ?int $ttl = null): void
    {
        $key = $this->getDynamicCategoriesCacheKey(categoryId: $categoryId, emirateId: $emirateId, regionId: $regionId, lang: $lang);
        $ttl = $ttl ?? $this->defaultTtl;
        
        Cache::put($key, $data, ttl: $ttl);
    }

    /**
     * Clear dynamic categories cache for specific category
     *
     * @param int $categoryId
     * @param int $emirateId
     * @param int $regionId
     * @param string|null $lang
     * @return void
     */
    public function clearDynamicCategoriesCache(int $categoryId, int $emirateId = 0, int $regionId = 0, ?string $lang = null): void
    {
        if ($lang) {
            Cache::forget($this->getDynamicCategoriesCacheKey(categoryId: $categoryId, emirateId: $emirateId, regionId: $regionId, lang: $lang));
        } else {
            // Clear for all languages
            $languages = ['en', 'ar'];
            foreach ($languages as $lang) {
                Cache::forget($this->getDynamicCategoriesCacheKey(categoryId: $categoryId, emirateId: $emirateId, regionId: $regionId, lang: $lang));
            }
        }
    }

    /**
     * Clear all dynamic categories cache
     *
     * @return void
     */
    public function clearAllDynamicCategoriesCache(): void
    {
        $emirateIds = Emirate::all()->pluck('id')->toArray();
        $languages = ['en', 'ar'];

        foreach ($emirateIds as $emirateId) {
            foreach ($languages as $lang) {
                Cache::forget($this->getDynamicCategoriesCacheKey(categoryId: 0, emirateId: $emirateId, regionId: 0, lang: $lang));
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
        return config('dynamiccategories.cache.enabled');
    }
}

