<?php

namespace Modules\DynamicShops\App\Console\Commands;

use App\Models\Emirate;
use App\Models\Region;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\DynamicShops\Enums\DynamicShopSectionType;
use Modules\DynamicShops\App\Models\DynamicShopSection;
use Modules\DynamicShops\App\Models\DynamicShopSectionItem;
use Modules\Shops\App\Models\Shop;
use Modules\Products\App\Models\Product;

class StoreDynamicShopSectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamicshops:store-sections 
                            {--force : Recreate items even if they exist}
                            {--sections=50 : Number of sections to create per shop}
                            {--items-per-section=100 : Number of items to add per section}
                            {--shops=10 : Number of shops to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample DynamicShops sections with items (idempotent)';

    public function handle(): int
    {
        $sectionsCount = (int) $this->option('sections');
        $itemsPerSection = (int) $this->option('items-per-section');
        $maxShops = (int) $this->option('shops');

        $this->info("Storing DynamicShops sections... (Sections: {$sectionsCount}, Items per section: {$itemsPerSection}, Shops: {$maxShops})");

        $emirateIds = array_map('strval', Emirate::forCountry('ae')->get()->pluck('id')->toArray());
        $regionIds = array_map('strval', Region::forCountry('ae')->get()->pluck('id')->toArray());

        $shops = Shop::forCountry('ae')->orderBy('id')->limit(1000)->get();
        $products = Product::forCountry('ae')->orderBy('id')->limit(1000)->get();

        if ($shops->isEmpty() || $products->isEmpty()) {
            $this->warn('Missing required base data (shop/product). Please seed/import data first.');
            return self::FAILURE;
        }

        DB::beginTransaction();
        try {
            $this->storeSections($shops, $products, $sectionsCount, $itemsPerSection, $maxShops, $emirateIds, $regionIds);

            DB::commit();
            $this->info('DynamicShops sections data stored successfully.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    private function storeSections($shops, $products, int $sectionsCount, int $itemsPerSection, int $maxShops, array $emirateIds, array $regionIds): void
    {
        $this->info("Storing DynamicShops sections... ({$sectionsCount} sections per shop)");
        
        // Limit shops to avoid creating too many sections
        $shopsToProcess = $shops->take(min($maxShops, $shops->count()));

        $sectionTypes = [
            DynamicShopSectionType::MENU_ITEMS => ['title_en' => 'Menu', 'title_ar' => 'القائمة'],
            DynamicShopSectionType::PRODUCTS => ['title_en' => 'Products', 'title_ar' => 'المنتجات', 'models' => $products],
            DynamicShopSectionType::BANNERS => ['title_en' => 'Banners', 'title_ar' => 'البانرات'],
        ];

        foreach ($shopsToProcess as $shopIndex => $shop) {
            $this->info("Shop {$shop->id}: Creating sections...");

            foreach ($sectionTypes as $type => $config) {
                for ($i = 0; $i < $sectionsCount; $i++) {
                    $section = DynamicShopSection::forCountry('ae')->firstOrCreate(
                        [
                            'shop_id' => $shop->id,
                            'type' => $type,
                            'title_en' => "{$config['title_en']} Section " . ($i + 1),
                            'sorting' => $i,
                        ],
                        [
                            'emirate_ids' => $emirateIds,
                            'region_ids' => $regionIds,
                            'title_ar' => "{$config['title_ar']} قسم " . ($i + 1),
                            'title_image_ar_url' => null,
                            'title_image_en_url' => null,
                            'background_image_url' => null,
                            'display_type' => $type === DynamicShopSectionType::MENU_ITEMS ? 'grid' : 'list',
                            'menu_type' => $type === DynamicShopSectionType::MENU_ITEMS ? 'horizontal' : null,
                            'item_type' => null,
                            'banner_size' => null,
                        ]
                    );

                    if ($type === DynamicShopSectionType::MENU_ITEMS) {
                        // Create menu item groups
                        $menuGroupsCount = min($itemsPerSection, 50);
                        $itemsToInsert = [];

                        for ($menuGroupId = 1; $menuGroupId <= $menuGroupsCount; $menuGroupId++) {
                            $menuItemWhere = [
                                'dynamic_shop_section_id' => $section->id,
                                'menu_item_parent_id' => $menuGroupId,
                            ];

                            if (Schema::hasColumn('dynamic_shop_section_items', 'is_all_menu_item')) {
                                $menuItemWhere['is_all_menu_item'] = $menuGroupId === 1;
                            }

                            $exists = DynamicShopSectionItem::forCountry('ae')->where($menuItemWhere)->exists();

                            if ($exists && ! $this->option('force')) {
                                continue;
                            }

                            $itemsToInsert[] = array_filter([
                                'dynamic_shop_section_id' => $section->id,
                                'menu_item_parent_id' => $menuGroupId,
                                'title_en' => "Menu Group {$menuGroupId}",
                                'title_ar' => "مجموعة القائمة {$menuGroupId}",
                                'image_ar_url' => null,
                                'image_en_url' => null,
                                'item_type' => null,
                                'item_id' => null,
                                'external_link' => null,
                                'sorting' => $menuGroupId - 1,
                                'is_all_menu_item' => Schema::hasColumn('dynamic_shop_section_items', 'is_all_menu_item') && $menuGroupId === 1 ? true : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ], static fn ($v) => $v !== null);
                        }

                        if (! empty($itemsToInsert)) {
                            if ($this->option('force')) {
                                DynamicShopSectionItem::forCountry('ae')
                                    ->where('dynamic_shop_section_id', $section->id)
                                    ->delete();
                            }

                            foreach (array_chunk($itemsToInsert, 500) as $chunk) {
                                DynamicShopSectionItem::forCountry('ae')->insert($chunk);
                            }
                        }
                    } elseif (isset($config['models'])) {
                        // Add products
                        $models = $config['models'];
                        $totalModels = $models->count();

                        if ($totalModels === 0) {
                            continue;
                        }

                        $itemsToAdd = min($itemsPerSection, $totalModels);
                        $modelsForSection = $models->shuffle()->take($itemsToAdd);

                        $itemsToInsert = [];
                        foreach ($modelsForSection as $sortIndex => $model) {
                            if (! $this->option('force')) {
                                $exists = DynamicShopSectionItem::forCountry('ae')
                                    ->where('dynamic_shop_section_id', $section->id)
                                    ->where('item_type', $model::class)
                                    ->where('item_id', $model->getKey())
                                    ->exists();

                                if ($exists) {
                                    continue;
                                }
                            }

                            $itemsToInsert[] = array_filter([
                                'dynamic_shop_section_id' => $section->id,
                                'menu_item_parent_id' => null,
                                'title_en' => null,
                                'title_ar' => null,
                                'image_ar_url' => null,
                                'image_en_url' => null,
                                'item_type' => $model::class,
                                'item_id' => $model->getKey(),
                                'external_link' => null,
                                'sorting' => $sortIndex,
                                'is_all_menu_item' => Schema::hasColumn('dynamic_shop_section_items', 'is_all_menu_item') ? false : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ], static fn ($v) => $v !== null);
                        }

                        if (! empty($itemsToInsert)) {
                            if ($this->option('force')) {
                                DynamicShopSectionItem::forCountry('ae')
                                    ->where('dynamic_shop_section_id', $section->id)
                                    ->delete();
                            }

                            foreach (array_chunk($itemsToInsert, 500) as $chunk) {
                                DynamicShopSectionItem::forCountry('ae')->insert($chunk);
                            }
                        }
                    }
                }
            }
        }
    }
}
