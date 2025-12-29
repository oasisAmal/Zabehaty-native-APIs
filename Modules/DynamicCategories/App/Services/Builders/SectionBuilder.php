<?php

namespace Modules\DynamicCategories\App\Services\Builders;

use App\Enums\Pagination;
use Modules\Shops\App\Models\Shop;
use Modules\Products\App\Models\Product;
use Modules\Categories\App\Models\Category;
use Modules\DynamicCategories\Enums\DynamicCategorySectionType;
use Modules\DynamicCategories\App\Models\DynamicCategorySection;
use Modules\DynamicCategories\App\Services\Builders\Factories\SectionBuilderFactory;
use Modules\Shops\App\Models\Scopes\MatchedDefaultAddressScope as ShopMatchedDefaultAddressScope;
use Modules\Products\App\Models\Scopes\MatchedDefaultAddressScope as ProductMatchedDefaultAddressScope;
use Modules\Categories\App\Models\Scopes\MatchedDefaultAddressScope as CategoryMatchedDefaultAddressScope;

class SectionBuilder
{
    protected SectionBuilderFactory $sectionBuilderFactory;

    public function __construct(SectionBuilderFactory $sectionBuilderFactory)
    {
        $this->sectionBuilderFactory = $sectionBuilderFactory;
    }

    /**
     * Build all active sections for a category
     *
     * @param int $categoryId
     * @return array
     */
    public function buildAll(int $categoryId): array
    {
        $dynamicCategorySections = DynamicCategorySection::ordered()
            ->where('category_id', $categoryId)
            ->has('items')
            ->with('items')
            ->get();

        $dynamicCategorySections->loadMorph('items.item', [
            Product::class => fn ($query) => $query->withoutGlobalScope(ProductMatchedDefaultAddressScope::class),
            Shop::class => fn ($query) => $query->withoutGlobalScope(ShopMatchedDefaultAddressScope::class),
            Category::class => fn ($query) => $query->withoutGlobalScope(CategoryMatchedDefaultAddressScope::class),
        ]);

        return $dynamicCategorySections
            ->map(function ($dynamicCategorySection) {
                return $this->buildSection($dynamicCategorySection);
            })
            ->filter(fn($section) => !empty($section['items']))
            ->values()
            ->toArray();
    }

    /**
     * Build a single section
     *
     * @param DynamicCategorySection $dynamicCategorySection
     * @return array
     */
    public function buildSection(DynamicCategorySection $dynamicCategorySection): array
    {
        $builder = $this->sectionBuilderFactory->create($dynamicCategorySection->type);

        $type = $dynamicCategorySection->type;
        if ($type == DynamicCategorySectionType::LIMITED_TIME_OFFERS) {
            $type = DynamicCategorySectionType::PRODUCTS;
        }

        return [
            'id' => $dynamicCategorySection->id,
            'type' => $type,
            'title' => $dynamicCategorySection->title,
            'title_image_url' => $dynamicCategorySection->title_image_url,
            'background_image_url' => $dynamicCategorySection->background_image_url,
            'display_type' => $dynamicCategorySection->display_type ? strtolower($dynamicCategorySection->display_type) : null,
            'menu_type' => $dynamicCategorySection->menu_type ? strtolower($dynamicCategorySection->menu_type) : null,
            'item_type' => $dynamicCategorySection->item_type ? strtolower($dynamicCategorySection->item_type) : null,
            'banner_size' => $dynamicCategorySection->banner_size ? strtolower($dynamicCategorySection->banner_size) : null,
            'sorting' => $dynamicCategorySection->sorting,
            // 'has_more_items' => $dynamicCategorySection->items->count() > Pagination::PER_PAGE,
            'has_more_items' => $builder->hasMoreItems($dynamicCategorySection),
            'items' => $builder->build($dynamicCategorySection),
        ];
    }
}

