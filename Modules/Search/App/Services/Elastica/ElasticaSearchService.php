<?php

namespace Modules\Search\App\Services\Elastica;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Search;
use Elastica\Suggest;
use Elastica\Suggest\Term;

/**
 * Search indices using Elastica query builder.
 *
 * Use Elastica\QueryBuilder\DSL or Elastica\Query classes to build queries,
 * then pass to search() or createSearch() for full control.
 *
 * @see https://elastica.io/getting-started/search-documents.html
 */
class ElasticaSearchService
{
    public function __construct(
        protected ElasticaClientService $clientService
    ) {}

    /**
     * Run a search on the given index(es).
     *
     * @param  string|string[]  $indexNames  Index name or list of index names
     * @param  Query|array|string  $query  Elastica Query, array, or empty string for match_all
     * @param  array{from?: int, size?: int, sort?: array}  $options
     */
    public function search(string|array $indexNames, Query|array|string $query = '', array $options = []): ResultSet
    {
        $search = $this->createSearch($indexNames, $query, $options);

        return $search->search('', null);
    }

    /**
     * Create a Search instance for full control (scroll, suggest, etc.).
     *
     * @param  string|string[]  $indexNames
     * @param  Query|array|string  $query
     * @param  array  $options
     */
    public function createSearch(string|array $indexNames, Query|array|string $query = '', array $options = []): Search
    {
        $client = $this->clientService->getClient();
        $search = new Search($client);

        $indexNames = is_array($indexNames) ? $indexNames : [$indexNames];
        foreach ($indexNames as $name) {
            $search->addIndexByName($name);
        }

        $search->setOptionsAndQuery($options, $query);

        return $search;
    }

    /**
     * Count documents matching the query.
     *
     * @param  string|string[]  $indexNames
     * @param  Query|array|string  $query
     */
    public function count(string|array $indexNames, Query|array|string $query = ''): int
    {
        $search = $this->createSearch($indexNames, $query);

        return $search->count('', false);
    }

    /**
     * Run term suggest on the given index(es) and field(s).
     *
     * @param  string|string[]  $indexNames
     * @param  string|string[]  $fields
     * @return array Raw suggest response from Elasticsearch
     */
    public function suggest(
        string|array $indexNames,
        string|array $fields,
        string $text,
        string $suggestionName = 'search_suggest',
        int $size = 10
    ): array {
        $fields = is_array($fields) ? array_values(array_unique($fields)) : [$fields];
        $suggest = new Suggest();

        foreach ($fields as $field) {
            $termSuggest = new Term("{$suggestionName}_{$field}", $field);
            $termSuggest->setText($text);
            $termSuggest->setSize($size);
            $termSuggest->setSuggestMode(Term::SUGGEST_MODE_POPULAR);
            $suggest->addSuggestion($termSuggest);
        }

        $client = $this->clientService->getClient();
        $search = new Search($client);

        $indexNames = is_array($indexNames) ? $indexNames : [$indexNames];
        foreach ($indexNames as $name) {
            $search->addIndexByName($name);
        }
        $search->setSuggest($suggest);

        $resultSet = $search->search('', null);

        $suggestions = $resultSet->hasSuggests() ? $resultSet->getSuggests() : [];
        return $this->normalizeSearchResults($suggestions, $fields, $text, $size);
    }

    /**
     * Normalize ES hits to flat suggestion strings from selected fields.
     *
     * @param  ResultSet  $resultSet
     * @param  array<int, string>  $fields
     * @param  string  $query
     * @param  int  $limit
     * @return array<int, string>
     */
    protected function normalizeSearchResults(array $suggestions, array $fields, string $query, int $limit): array
    {
        $suggestions = [];
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $displayField = $locale === 'ar' ? 'name' : 'name_en';
        $fallbackField = $locale === 'ar' ? 'name_en' : 'name';

        foreach ($suggestions as $suggestion => $suggestionData) {
            $source = $suggestionData['options'];
            $matched = false;

            foreach ($fields as $field) {
                $value = $source[$field] ?? null;
                if (!is_string($value) || $value === '') {
                    continue;
                }

                if (mb_stripos($value, $query) !== false) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                continue;
            }

            $displayValue = $source[$displayField] ?? null;
            if (!is_string($displayValue) || $displayValue === '') {
                $displayValue = $source[$fallbackField] ?? null;
            }

            if (is_string($displayValue) && $displayValue !== '') {
                $suggestions[] = $displayValue;
            }
        }

        return collect($suggestions)->unique()->slice(0, $limit)->toArray();
    }
}
