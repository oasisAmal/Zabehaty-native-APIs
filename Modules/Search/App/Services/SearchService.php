<?php

namespace Modules\Search\App\Services;

use Elastica\ResultSet;
use Modules\Search\App\Services\Builders\SectionBuilder;
use Modules\Search\App\Services\Cache\CacheService;
use Modules\Search\App\Services\Elastica\ElasticaIndexService;
use Modules\Search\App\Services\Elastica\ElasticaSearchService;
use Modules\Search\Enums\SearchSectionType;

class SearchService
{
    public function __construct(
        protected SectionBuilder $sectionBuilder,
        protected CacheService $cacheService,
        protected ElasticaSearchService $elasticaSearchService,
        protected ElasticaIndexService $elasticaIndexService
    ) {
    }

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
     * Get search suggestions from Elasticsearch using multi-field search.
     *
     * @param  array  $data
     * @return array<int, string>
     */
    public function getSearchSuggestions(array $data): array
    {
        $query = trim((string) ($data['q'] ?? ''));
        if ($query === '') {
            return [];
        }

        $limit = (int) ($data['limit'] ?? 10);

        $indexName = config('search.elasticsearch_index_names.products');
        $suggestFields = config('search.elasticsearch_suggest_fields.products');

        try {
            if (!$this->elasticaIndexService->exists($indexName)) {
                return [];
            }

            $resultSet = $this->elasticaSearchService->search(
                $indexName,
                [
                    '_source' => $suggestFields,
                    'size' => $limit,
                    'query' => [
                        'bool' => [
                            'should' => [
                                [
                                    'multi_match' => [
                                        'query' => $query,
                                        'fields' => $suggestFields,
                                        'type' => 'phrase_prefix',
                                    ],
                                ],
                                [
                                    'multi_match' => [
                                        'query' => $query,
                                        'fields' => $suggestFields,
                                        'type' => 'best_fields',
                                        'operator' => 'and',
                                    ],
                                ],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ],
                ]
            );

            return $this->normalizeSearchResults($resultSet, $suggestFields, $query, $limit);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Normalize ES hits to flat suggestion strings from selected fields.
     *
     * @param  array<int, string>  $fields
     * @return array<int, string>
     */
    protected function normalizeSearchResults(ResultSet $resultSet, array $fields, string $query, int $limit): array
    {
        $suggestions = [];

        foreach ($resultSet->getResults() as $result) {
            $source = $result->getData();
            foreach ($fields as $field) {
                $value = $source[$field] ?? null;
                if (!is_string($value) || $value === '') {
                    continue;
                }

                if (mb_stripos($value, $query) !== false) {
                    $suggestions[] = $value;
                }
            }
        }

        return collect($suggestions)->unique()->slice(0, $limit)->toArray();
    }
}
