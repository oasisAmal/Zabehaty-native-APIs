<?php

namespace Modules\DynamicShops\App\Services\Builders\Sections;

use Modules\DynamicShops\App\Models\DynamicShopSection;
use Modules\DynamicShops\App\Services\Builders\Interfaces\SectionBuilderInterface;

class DefaultSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build default section data
     *
     * @param DynamicShopSection $dynamicShopSection
     * @return array
     */
    public function build(DynamicShopSection $dynamicShopSection): array
    {
        return [];
    }

    /**
     * Check if there are more items to load
     *
     * @param DynamicShopSection $dynamicShopSection
     * @return bool
     */
    public function hasMoreItems(DynamicShopSection $dynamicShopSection): bool
    {
        return false;
    }
}
