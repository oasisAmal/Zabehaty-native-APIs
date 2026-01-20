<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Shops\App\Models\Shop;
use Modules\Products\App\Models\Product;
use Modules\Categories\App\Models\Category;
use App\Models\Emirate;


class RequiredDataUpdatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'requiredData:updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Required Data Updates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Updating required data...');
        $this->activeAllShops();
        $this->activeAllCategories();
        $this->activeAllProducts();
        $this->updateAllProductsPrice();
        $this->updateAllProdctsVisibility();
        $this->updateAllCategoriesVisibility();
        $this->updateAllShopsVisibility();
        $this->assignCategoryToProducts();
        $this->info('Required data updated successfully');
    }

    public function activeAllShops()
    {
        $this->info('Activating all shops...');
        Shop::where('is_active', 0)->update(['is_active' => 1]);
        $this->info('All shops activated successfully');
    }

    public function activeAllCategories()
    {
        $this->info('Activating all categories...');
        Category::where('is_active', 0)->update(['is_active' => 1]);
        $this->info('All categories activated successfully');
    }

    public function activeAllProducts()
    {
        $this->info('Activating all products...');
        Product::where('is_active', 0)->update(['is_active' => 1, 'is_approved' => 1]);
        $this->info('All products activated successfully');
    }

    public function updateAllProductsPrice()
    {
        $this->info('Updating all products price...');
        Product::with('subProducts')->orderBy('id', 'desc')->chunk(100, function ($products) {
            foreach ($products as $product) {
                if ($product->has_sub_products) {
                    $product->subProducts->each(function ($subProduct) {
                        $subProduct->price = 100;
                        $subProduct->save();
                    });
                    $product->price = 100;
                } else {
                    $product->price = 200;
                }
                $product->save();
            }
        });
        $this->info('All products price updated successfully');
    }

    public function updateAllProdctsVisibility()
    {
        $this->info('Updating all products visibility...');
        Product::with('productVisibilities')->orderBy('id', 'desc')->chunk(100, function ($products) {
            foreach ($products as $product) {
                $product->productVisibilities()->delete();
                Emirate::with('regions')->get()->each(function ($emirate) use ($product) {
                    $product->productVisibilities()->create([
                        'emirate_id' => $emirate->id,
                        'region_ids' => $emirate->regions->pluck('id')->toArray(),
                    ]);
                });
            }
        });
        $this->info('All products visibility updated successfully');
    }

    public function updateAllCategoriesVisibility()
    {
        $this->info('Updating all categories visibility...');
        Category::with('categoryVisibilities')->orderBy('id', 'desc')->chunk(100, function ($categories) {
            foreach ($categories as $category) {
                $category->categoryVisibilities()->delete();
                Emirate::with('regions')->get()->each(function ($emirate) use ($category) {
                    $category->categoryVisibilities()->create([
                        'emirate_id' => $emirate->id,
                        'region_ids' => $emirate->regions->pluck('id')->toArray(),
                    ]);
                });
            }
        });
        $this->info('All categories visibility updated successfully');
    }

    public function updateAllShopsVisibility()
    {
        $this->info('Updating all shops visibility...');
        Shop::with('shopsVisibilities')->orderBy('id', 'desc')->chunk(100, function ($shops) {
            foreach ($shops as $shop) {
                $shop->shopsVisibilities()->delete();
                Emirate::with('regions')->get()->each(function ($emirate) use ($shop) {
                    $shop->shopsVisibilities()->create([
                        'emirate_id' => $emirate->id,
                        'region_ids' => $emirate->regions->pluck('id')->toArray(),
                    ]);
                });
            }
        });
        $this->info('All shops visibility updated successfully');
    }

    public function assignCategoryToProducts()
    {
        $this->info('Assigning category to products...');
        Product::whereNull('category_id')->orWhereNull('department_id')->orderBy('id', 'desc')->chunk(100, function ($products) {
            foreach ($products as $product) {
                if (!$product->category) {
                    $product->category_id = 247;
                }
                if (!$product->department) {
                    $product->department_id = 247;
                }
                $product->save();
            }
        });
        $this->info('Category assigned to products successfully');
    }
}
