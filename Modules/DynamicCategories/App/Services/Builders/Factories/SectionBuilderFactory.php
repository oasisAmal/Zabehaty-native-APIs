<?php

namespace Modules\DynamicCategories\App\Services\Builders\Factories;

use Modules\DynamicCategories\Enums\DynamicCategorySectionType;
use Modules\DynamicCategories\App\Services\Builders\Sections\BannerSectionBuilder;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\DynamicCategories\App\Services\Builders\Sections\MenuItemsSectionBuilder;
use Modules\DynamicCategories\App\Services\Builders\Sections\DefaultSectionBuilder;
use Modules\DynamicCategories\App\Services\Builders\Sections\ProductSectionBuilder;
use Modules\DynamicCategories\App\Services\Builders\Sections\ShopSectionBuilder;

class SectionBuilderFactory
{
    /**
     * Create appropriate section builder based on type
     *
     * @param string $type
     * @return SectionBuilderInterface
     */
    public function create($type): SectionBuilderInterface
    {
        return match ($type) {
            DynamicCategorySectionType::BANNERS => new BannerSectionBuilder(),
            // DynamicCategorySectionType::SHOPS => new ShopSectionBuilder(),
            DynamicCategorySectionType::MENU_ITEMS => new MenuItemsSectionBuilder(),
            DynamicCategorySectionType::LIMITED_TIME_OFFERS,
            DynamicCategorySectionType::PRODUCTS => new ProductSectionBuilder(),
            default => new DefaultSectionBuilder(),
        };
    }
}

