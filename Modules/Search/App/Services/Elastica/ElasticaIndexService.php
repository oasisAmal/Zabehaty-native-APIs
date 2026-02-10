<?php

namespace Modules\Search\App\Services\Elastica;

use Elastica\Index;
use Elastica\Mapping;
use Elastica\Response;
use Elastica\Bulk\ResponseSet;

/**
 * Index management, mapping and document indexing via Elastica.
 *
 * @see https://elastica.io/getting-started/storing-and-indexing-documents.html
 */
class ElasticaIndexService
{
    public function __construct(
        protected ElasticaClientService $clientService
    ) {
    }

    public function getIndex(string $name): Index
    {
        return $this->clientService->getClient()->getIndex($name);
    }

    /**
     * Create an index with optional settings (shards, replicas, analysis).
     *
     * @param  array{number_of_shards?: int, number_of_replicas?: int, analysis?: array}  $settings
     */
    public function createIndex(string $name, array $settings = [], bool $recreate = false): Response
    {
        $index = $this->getIndex($name);

        return $index->create($settings, ['recreate' => $recreate]);
    }

    /**
     * Set mapping properties for the index.
     *
     * @param  array<string, array<string, mixed>>  $properties  e.g. ['title' => ['type' => 'text'], 'created_at' => ['type' => 'date']]
     */
    public function setMapping(string $indexName, array $properties, array $query = []): Response
    {
        $index = $this->getIndex($indexName);
        $mapping = new Mapping($properties);

        return $mapping->send($index, $query);
    }

    /**
     * Add a single document to the index.
     */
    public function addDocument(string $indexName, string|int $id, array $data): Response
    {
        $index = $this->getIndex($indexName);
        $doc = $index->createDocument((string) $id, $data);

        return $index->addDocument($doc);
    }

    /**
     * Bulk add documents. Each item: ['id' => string|int, 'data' => array]
     *
     * @param  array<int, array{id: string|int, data: array}>  $documents
     */
    public function addDocuments(string $indexName, array $documents, array $options = []): ResponseSet
    {
        $index = $this->getIndex($indexName);
        $docs = [];
        foreach ($documents as $item) {
            $docs[] = $index->createDocument(
                (string) ($item['id'] ?? ''),
                $item['data'] ?? $item
            );
        }

        return $index->addDocuments($docs, $options);
    }

    /**
     * Refresh the index so new documents are searchable.
     */
    public function refresh(string $indexName): Response
    {
        return $this->getIndex($indexName)->refresh();
    }

    /**
     * Delete the index.
     */
    public function deleteIndex(string $name): Response
    {
        return $this->getIndex($name)->delete();
    }

    /**
     * Check if the index exists.
     */
    public function exists(string $name): bool
    {
        return $this->getIndex($name)->exists();
    }
}
