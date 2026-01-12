<?php

namespace Modules\DynamicShops\App\Services\Builders\Factories;

use Modules\DynamicShops\Enums\DynamicShopSectionType;
use Modules\DynamicShops\App\Services\Builders\Sections\BannerSectionBuilder;
use Modules\DynamicShops\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\DynamicShops\App\Services\Builders\Sections\MenuItemsSectionBuilder;
use Modules\DynamicShops\App\Services\Builders\Sections\DefaultSectionBuilder;
use Modules\DynamicShops\App\Services\Builders\Sections\ProductSectionBuilder;
use Modules\DynamicShops\App\Services\Builders\Sections\ShopSectionBuilder;

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
            DynamicShopSectionType::BANNERS => new BannerSectionBuilder(),
            DynamicShopSectionType::SHOPS => new ShopSectionBuilder(),
            DynamicShopSectionType::MENU_ITEMS => new MenuItemsSectionBuilder(),
            DynamicShopSectionType::LIMITED_TIME_OFFERS,
            DynamicShopSectionType::PRODUCTS => new ProductSectionBuilder(),
            default => new DefaultSectionBuilder(),
        };
    }
}
