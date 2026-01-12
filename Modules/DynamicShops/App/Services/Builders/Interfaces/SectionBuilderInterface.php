<?php

namespace Modules\DynamicShops\App\Services\Builders\Interfaces;

use Modules\DynamicShops\App\Models\DynamicShopSection;

interface SectionBuilderInterface
{
    /**
     * Build section data
     *
     * @param DynamicShopSection $dynamicShopSection
     * @return array
     */
    public function build(DynamicShopSection $dynamicShopSection): array;

    /**
     * Check if there are more items to load
     *
     * @param DynamicShopSection $dynamicShopSection
     * @return bool
     */
    public function hasMoreItems(DynamicShopSection $dynamicShopSection): bool;
}
