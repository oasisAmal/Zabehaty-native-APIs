<?php

namespace Modules\DynamicShops\App\Services\Builders;

use App\Enums\Pagination;
use Modules\Shops\App\Models\Shop;
use Modules\Products\App\Models\Product;
use Modules\Categories\App\Models\Category;
use Modules\DynamicShops\Enums\DynamicShopSectionType;
use Modules\DynamicShops\App\Models\DynamicShopSection;
use Modules\DynamicShops\App\Services\Builders\Factories\SectionBuilderFactory;
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
     * Build all active sections for a shop
     *
     * @param int $shopId
     * @return array
     */
    public function buildAll(int $shopId): array
    {
        $dynamicShopSections = DynamicShopSection::ordered()
            ->where('shop_id', $shopId)
            ->has('items')
            ->with('items')
            ->get();

        $dynamicShopSections->loadMorph('items.item', [
            Product::class => fn ($query) => $query->withoutGlobalScope(ProductMatchedDefaultAddressScope::class),
            Shop::class => fn ($query) => $query->withoutGlobalScope(ShopMatchedDefaultAddressScope::class),
            Category::class => fn ($query) => $query->withoutGlobalScope(CategoryMatchedDefaultAddressScope::class),
        ]);

        return $dynamicShopSections
            ->map(function ($dynamicShopSection) {
                return $this->buildSection($dynamicShopSection);
            })
            ->filter(fn($section) => !empty($section['items']))
            ->values()
            ->toArray();
    }

    /**
     * Build a single section
     *
     * @param DynamicShopSection $dynamicShopSection
     * @return array
     */
    public function buildSection(DynamicShopSection $dynamicShopSection): array
    {
        $builder = $this->sectionBuilderFactory->create($dynamicShopSection->type);

        $type = $dynamicShopSection->type;
        if ($type == DynamicShopSectionType::LIMITED_TIME_OFFERS) {
            $type = DynamicShopSectionType::PRODUCTS;
        }

        return [
            'id' => $dynamicShopSection->id,
            'type' => $type,
            'title' => $dynamicShopSection->title,
            'title_image_url' => $dynamicShopSection->title_image_url,
            'background_image_url' => $dynamicShopSection->background_image_url,
            'display_type' => $dynamicShopSection->display_type ? strtolower($dynamicShopSection->display_type) : null,
            'menu_type' => $dynamicShopSection->menu_type ? strtolower($dynamicShopSection->menu_type) : null,
            'item_type' => $dynamicShopSection->item_type ? strtolower($dynamicShopSection->item_type) : null,
            'banner_size' => $dynamicShopSection->banner_size ? strtolower($dynamicShopSection->banner_size) : null,
            'sorting' => $dynamicShopSection->sorting,
            'has_more_items' => $builder->hasMoreItems($dynamicShopSection),
            'items' => $builder->build($dynamicShopSection),
        ];
    }
}
