<?php

namespace Modules\HomePage\App\Console\Commands;

use App\Models\Region;
use App\Models\Emirate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Shops\App\Models\Shop;
use Modules\Products\App\Models\Product;
use Modules\HomePage\App\Models\HomePage;
use Modules\Categories\App\Models\Category;
use Modules\HomePage\Enums\HomeSectionType;
use Modules\HomePage\App\Models\HomePageItem;

class StoreHomePageSectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'homepage:store-sections 
                            {--force : Recreate items even if they exist}
                            {--sections=50 : Number of sections to create per type}
                            {--items-per-section=100 : Number of items to add per section}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample HomePage sections with items (idempotent)';

    public function handle(): int
    {
        $sectionsCount = (int) $this->option('sections');
        $itemsPerSection = (int) $this->option('items-per-section');

        $this->info("Storing HomePage sections... (Sections: {$sectionsCount}, Items per section: {$itemsPerSection})");

        $shops = Shop::forCountry('ae')->limit(1000)->get();
        $products = Product::forCountry('ae')->limit(1000)->get();
        $categories = Category::forCountry('ae')->limit(1000)->get();

        if ($shops->isEmpty() || $products->isEmpty() || $categories->isEmpty()) {
            $this->warn('Missing required base data (shop/product/category). Please seed/import data first.');
            return self::FAILURE;
        }

        DB::beginTransaction();
        try {
            $this->storeSections($categories, $shops, $products, $sectionsCount, $itemsPerSection);

            DB::commit();
            $this->info('HomePage sections data stored successfully.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    private function storeSections($categories, $shops, $products, int $sectionsCount, int $itemsPerSection): void
    {
        $sections = [
            ['type' => HomeSectionType::CATEGORIES, 'title_en' => 'Categories', 'title_ar' => 'التصنيفات', 'models' => $categories],
            ['type' => HomeSectionType::SHOPS, 'title_en' => 'Shops', 'title_ar' => 'المتاجر', 'models' => $shops],
            ['type' => HomeSectionType::PRODUCTS, 'title_en' => 'Products', 'title_ar' => 'المنتجات', 'models' => $products],
        ];

        foreach ($sections as $section) {
            if ($section['models']->isEmpty()) {
                $this->warn("No {$section['title_en']} found. Skipping...");
                continue;
            }

            $this->info("Creating {$sectionsCount} {$section['title_en']} sections...");

            for ($i = 0; $i < $sectionsCount; $i++) {
                // Create section
                $homePage = HomePage::createForCountry(
                    [
                        'emirate_ids' => array_map('strval', Emirate::forCountry('ae')->get()->pluck('id')->toArray()),
                        'region_ids' => array_map('strval', Region::forCountry('ae')->get()->pluck('id')->toArray()),
                        'type' => $section['type'],
                        'title_en' => "{$section['title_en']} Section " . ($i + 1),
                        'title_ar' => "{$section['title_ar']} قسم " . ($i + 1),
                        'sorting' => $i,
                    ],
                    'ae',
                );

                // Get random items
                $items = $section['models']->shuffle()->take(min($itemsPerSection, $section['models']->count()));

                // Insert items
                $itemsData = [];
                $now = now();
                foreach ($items as $model) {
                    $itemsData[] = [
                        'home_page_id' => $homePage->id,
                        'item_type' => get_class($model),
                        'item_id' => $model->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($itemsData)) {
                    foreach (array_chunk($itemsData, 500) as $chunk) {
                        HomePageItem::forCountry('ae')->insert($chunk);
                    }
                    $this->info("  Section " . ($i + 1) . ": Added " . count($itemsData) . " items");
                }
            }
        }
    }
}
