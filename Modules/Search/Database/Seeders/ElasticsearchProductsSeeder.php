<?php

namespace Modules\Search\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Traits\CountryQueryBuilderTrait;
use Modules\Search\App\Services\Elastica\ElasticaIndexService;

class ElasticsearchProductsSeeder extends Seeder
{
    use CountryQueryBuilderTrait;

    protected int $chunkSize = 500;

    public function __construct(
        protected ElasticaIndexService $indexService
    ) {
    }

    /**
     * Run the database seeds.
     * Indexes products from the database into Elasticsearch for the current country.
     */
    public function run(): void
    {
        $indexName = config('search.elasticsearch_index_names.products');
        $esHost = config('elastica.hosts.0', 'not set');
        $this->command->info("Elasticsearch: {$esHost}");
        $this->command->info("Creating index: {$indexName}");

        try {
            $this->ensureIndexAndMapping($indexName);
        } catch (\Elastic\Transport\Exception\NoNodeAvailableException $e) {
            dd('error', $e->getMessage());
            $this->command->error('Cannot reach Elasticsearch. When running in Docker use ELASTICSEARCH_HOST=elasticsearch in .env and run: php artisan config:clear');
            throw $e;
        }

        $this->command->info('Indexing products...');

        $indexed = 0;

        $query = $this->getCountryConnection()
            ->table('products')
            ->select([
                'products.id',
                'products.name',
                'products.name_en',
                'products.description',
                'products.description_en',
                'products.category_id',
                'products.shop_id',
                'products.price',
                'products.old_price',
                'products.is_active',
            ])
            ->where('products.is_active', true)
            ->where('products.is_approved', true)
            ->whereNotNull('products.department_id')
            ->orderBy('products.id');

        $query->chunk($this->chunkSize, function ($products) use ($indexName, &$indexed) {
                $documents = [];
                foreach ($products as $product) {
                    $documents[] = [
                        'id' => (string) $product->id,
                        'data' => $this->productToDocument($product),
                    ];
                }
                $this->indexService->addDocuments($indexName, $documents);
                $indexed += count($documents);
                $this->command->getOutput()->write('.');
            });

        $this->indexService->refresh($indexName);

        $this->command->newLine();
        $this->command->info("Indexed {$indexed} products into [{$indexName}].");
    }

    protected function ensureIndexAndMapping(string $indexName): void
    {
        if (!$this->indexService->exists($indexName)) {
            $this->indexService->createIndex($indexName, [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                ],
            ]);
        } else {
            $this->command->warn("Index [{$indexName}] already exists. Updating mapping.");
        }

        $this->indexService->setMapping($indexName, [
            'name' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
            'name_ar' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
            'name_en' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
            'description' => ['type' => 'text'],
            'description_en' => ['type' => 'text'],
            'category_id' => ['type' => 'integer'],
            'shop_id' => ['type' => 'integer'],
            'price' => ['type' => 'float'],
            'old_price' => ['type' => 'float'],
            'is_active' => ['type' => 'boolean'],
        ]);
    }

    /**
     * @param  object  $product  StdClass from query (id, name, name_en, ...)
     */
    protected function productToDocument(object $product): array
    {
        $nameAr = $product->name ?? '';
        $nameEn = $product->name_en ?? '';
        $name = $nameAr ?: $nameEn;
        $description = $product->description ?? '';
        $descriptionEn = $product->description_en ?? '';

        return [
            'name' => $name,
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'description' => $description,
            'description_en' => $descriptionEn,
            'category_id' => (int) ($product->category_id ?? 0),
            'shop_id' => isset($product->shop_id) ? (int) $product->shop_id : null,
            'price' => (float) ($product->price ?? 0),
            'old_price' => isset($product->old_price) ? (float) $product->old_price : null,
            'is_active' => (bool) ($product->is_active ?? true),
        ];
    }
}
