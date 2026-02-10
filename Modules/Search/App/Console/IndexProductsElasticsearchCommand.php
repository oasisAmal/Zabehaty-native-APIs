<?php

namespace Modules\Search\App\Console;

use Illuminate\Console\Command;
use Modules\Search\Database\Seeders\ElasticsearchProductsSeeder;

class IndexProductsElasticsearchCommand extends Command
{
    protected $signature = 'search:index-products';

    protected $description = 'Index products from database into Elasticsearch';

    public function handle(ElasticsearchProductsSeeder $seeder): int
    {
        $this->info('Running Elasticsearch products indexer...');
        $seeder->setCommand($this);
        $seeder->run();

        return self::SUCCESS;
    }
}
