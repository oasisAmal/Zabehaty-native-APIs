<?php

namespace Modules\Search\App\Services;

use Modules\Products\App\Models\Product;
use Modules\Search\Enums\SearchTypes;
use Modules\Search\Enums\SearchSectionType;
use Modules\Search\App\Services\Cache\CacheService;
use Modules\Search\App\Services\Builders\SectionBuilder;
use Modules\Shops\App\Models\Shop;

class SearchService
{
    public function __construct(
        protected SectionBuilder $sectionBuilder,
        protected CacheService $cacheService,
    ) {}

    /**
     * Prepare search data
     *
     * @return array
     */
    public function getSearchData(): array
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

    /**
     * Get search suggestions from MySQL (products and/or shops).
     *
     * @param  array  $data
     * @return array<int, array{id: int, name: string, type: string}>
     */
    public function getSearchSuggestions(array $data): array
    {
        $query = trim((string) ($data['q'] ?? ''));
        if ($query === '') {
            return [];
        }

        $limit = (int) ($data['limit'] ?? 10);
        $type = $data['type'] ?? SearchTypes::ALL;
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';
        
        $searchPattern = '%' . normalizeArabicText($query) . '%';
        $nameSql = arabicNormalizeColumnSql('name');
        $nameEnSql = arabicNormalizeColumnSql('name_en');

        $nameFilter = function ($q) use ($searchPattern, $nameSql, $nameEnSql) {
            $q->whereRaw("{$nameSql} LIKE ?", [$searchPattern])
                ->orWhereRaw("{$nameEnSql} LIKE ?", [$searchPattern]);
        };

        if ($type === SearchTypes::PRODUCTS) {
            return $this->getProductSuggestions($nameColumn, $nameFilter, $limit);
        }

        if ($type === SearchTypes::SHOPS) {
            return $this->getShopSuggestions($nameColumn, $nameFilter, $limit);
        }

        $products = $this->getProductSuggestions($nameColumn, $nameFilter, $limit);
        $shops = $this->getShopSuggestions($nameColumn, $nameFilter, $limit);
        $merged = array_merge($products, $shops);

        return array_slice($merged, 0, $limit);
    }

    /**
     * @param  string  $nameColumn
     * @param  callable  $nameFilter
     * @param  int  $limit
     * @return array<int, array{id: int, name: string, type: string}>
     */
    private function getProductSuggestions(string $nameColumn, callable $nameFilter, int $limit): array
    {
        return Product::query()
            ->where($nameFilter)
            ->select('id', $nameColumn)
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                // 'id' => $row->id,
                'name' => $row->{$nameColumn},
                'type' => 'product',
            ])
            ->values()
            ->all();
    }

    /**
     * @param  string  $nameColumn
     * @param  callable  $nameFilter
     * @param  int  $limit
     * @return array<int, array{id: int, name: string, type: string}>
     */
    private function getShopSuggestions(string $nameColumn, callable $nameFilter, int $limit): array
    {
        return Shop::query()
            ->where($nameFilter)
            ->select('id', $nameColumn)
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                // 'id' => $row->id,
                'name' => $row->{$nameColumn},
                'type' => 'shop',
            ])
            ->values()
            ->all();
    }
}
