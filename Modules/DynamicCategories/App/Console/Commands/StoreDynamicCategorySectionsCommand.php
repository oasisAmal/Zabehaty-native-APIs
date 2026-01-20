<?php

namespace Modules\DynamicCategories\App\Console\Commands;

use App\Models\Emirate;
use App\Models\Region;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\DynamicCategories\Enums\DynamicCategorySectionType;
use Modules\DynamicCategories\App\Models\DynamicCategorySection;
use Modules\DynamicCategories\App\Models\DynamicCategorySectionItem;
use Modules\Shops\App\Models\Shop;
use Modules\Products\App\Models\Product;
use Modules\Categories\App\Models\Category;

class StoreDynamicCategorySectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamiccategories:store-sections 
                            {--force : Recreate items even if they exist}
                            {--sections=50 : Number of sections to create per category}
                            {--items-per-section=100 : Number of items to add per section}
                            {--categories=10 : Number of categories to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample DynamicCategories sections with items (idempotent)';

    public function handle(): int
    {
        $sectionsCount = (int) $this->option('sections');
        $itemsPerSection = (int) $this->option('items-per-section');
        $maxCategories = (int) $this->option('categories');

        $this->info("Storing DynamicCategories sections... (Sections: {$sectionsCount}, Items per section: {$itemsPerSection}, Categories: {$maxCategories})");

        $emirateIds = array_map('strval', Emirate::forCountry('ae')->get()->pluck('id')->toArray());
        $regionIds = array_map('strval', Region::forCountry('ae')->get()->pluck('id')->toArray());

        $shops = Shop::forCountry('ae')->orderBy('id')->limit(1000)->get();
        $products = Product::forCountry('ae')->orderBy('id')->limit(1000)->get();
        $categories = Category::forCountry('ae')->orderBy('id')->limit(1000)->get();

        if ($shops->isEmpty() || $products->isEmpty() || $categories->isEmpty()) {
            $this->warn('Missing required base data (shop/product/category). Please seed/import data first.');
            return self::FAILURE;
        }

        DB::beginTransaction();
        try {
            $this->storeSections($categories, $shops, $products, $sectionsCount, $itemsPerSection, $maxCategories, $emirateIds, $regionIds);

            DB::commit();
            $this->info('DynamicCategories sections data stored successfully.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    private function storeSections($categories, $shops, $products, int $sectionsCount, int $itemsPerSection, int $maxCategories, array $emirateIds, array $regionIds): void
    {
        $this->info("Storing DynamicCategories sections... ({$sectionsCount} sections per category)");
        
        // Limit categories to avoid creating too many sections
        $categoriesToProcess = $categories->take(min($maxCategories, $categories->count()));

        $sectionTypes = [
            DynamicCategorySectionType::MENU_ITEMS => ['title_en' => 'Menu', 'title_ar' => 'القائمة'],
            DynamicCategorySectionType::PRODUCTS => ['title_en' => 'Products', 'title_ar' => 'المنتجات', 'models' => $products],
            DynamicCategorySectionType::SHOPS => ['title_en' => 'Shops', 'title_ar' => 'المتاجر', 'models' => $shops],
            DynamicCategorySectionType::BANNERS => ['title_en' => 'Banners', 'title_ar' => 'البانرات'],
        ];

        foreach ($categoriesToProcess as $categoryIndex => $category) {
            $this->info("Category {$category->id}: Creating sections...");

            foreach ($sectionTypes as $type => $config) {
                for ($i = 0; $i < $sectionsCount; $i++) {
                    $section = DynamicCategorySection::forCountry('ae')->firstOrCreate(
                        [
                            'category_id' => $category->id,
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
                            'display_type' => $type === DynamicCategorySectionType::MENU_ITEMS ? 'grid' : 'list',
                            'menu_type' => $type === DynamicCategorySectionType::MENU_ITEMS ? 'horizontal' : null,
                            'item_type' => null,
                            'banner_size' => null,
                        ]
                    );

                    if ($type === DynamicCategorySectionType::MENU_ITEMS) {
                        // Create menu item groups
                        $menuGroupsCount = min($itemsPerSection, 50);
                        $itemsToInsert = [];

                        for ($menuGroupId = 1; $menuGroupId <= $menuGroupsCount; $menuGroupId++) {
                            $menuItemWhere = [
                                'dynamic_category_section_id' => $section->id,
                                'menu_item_parent_id' => $menuGroupId,
                            ];

                            if (Schema::hasColumn('dynamic_category_section_items', 'is_all_menu_item')) {
                                $menuItemWhere['is_all_menu_item'] = $menuGroupId === 1;
                            }

                            $exists = DynamicCategorySectionItem::forCountry('ae')->where($menuItemWhere)->exists();

                            if ($exists && ! $this->option('force')) {
                                continue;
                            }

                            $itemsToInsert[] = array_filter([
                                'dynamic_category_section_id' => $section->id,
                                'menu_item_parent_id' => $menuGroupId,
                                'title_en' => "Menu Group {$menuGroupId}",
                                'title_ar' => "مجموعة القائمة {$menuGroupId}",
                                'image_ar_url' => null,
                                'image_en_url' => null,
                                'item_type' => null,
                                'item_id' => null,
                                'external_link' => null,
                                'sorting' => $menuGroupId - 1,
                                'is_all_menu_item' => Schema::hasColumn('dynamic_category_section_items', 'is_all_menu_item') && $menuGroupId === 1 ? true : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ], static fn ($v) => $v !== null);
                        }

                        if (! empty($itemsToInsert)) {
                            if ($this->option('force')) {
                                DynamicCategorySectionItem::forCountry('ae')
                                    ->where('dynamic_category_section_id', $section->id)
                                    ->delete();
                            }

                            foreach (array_chunk($itemsToInsert, 500) as $chunk) {
                                DynamicCategorySectionItem::forCountry('ae')->insert($chunk);
                            }
                        }
                    } elseif (isset($config['models'])) {
                        // Add products or shops
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
                                $exists = DynamicCategorySectionItem::forCountry('ae')
                                    ->where('dynamic_category_section_id', $section->id)
                                    ->where('item_type', $model::class)
                                    ->where('item_id', $model->getKey())
                                    ->exists();

                                if ($exists) {
                                    continue;
                                }
                            }

                            $itemsToInsert[] = array_filter([
                                'dynamic_category_section_id' => $section->id,
                                'menu_item_parent_id' => null,
                                'title_en' => null,
                                'title_ar' => null,
                                'image_ar_url' => null,
                                'image_en_url' => null,
                                'item_type' => $model::class,
                                'item_id' => $model->getKey(),
                                'external_link' => null,
                                'sorting' => $sortIndex,
                                'is_all_menu_item' => Schema::hasColumn('dynamic_category_section_items', 'is_all_menu_item') ? false : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ], static fn ($v) => $v !== null);
                        }

                        if (! empty($itemsToInsert)) {
                            if ($this->option('force')) {
                                DynamicCategorySectionItem::forCountry('ae')
                                    ->where('dynamic_category_section_id', $section->id)
                                    ->delete();
                            }

                            foreach (array_chunk($itemsToInsert, 500) as $chunk) {
                                DynamicCategorySectionItem::forCountry('ae')->insert($chunk);
                            }
                        }
                    }
                }
            }
        }
    }
}
