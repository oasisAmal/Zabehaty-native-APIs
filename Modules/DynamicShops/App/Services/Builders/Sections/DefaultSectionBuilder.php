<?php

namespace Modules\DynamicShops\App\Services\Builders\Sections;

use Modules\DynamicShops\App\Services\Builders\Interfaces\SectionBuilderInterface;

class DefaultSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build default section data
     *
     * @param array $dynamicShopSection
     * @return array
     */
    public function build(array $dynamicShopSection): array
    {
        return [];
    }

    /**
     * Check if there are more items to load
     *
     * @param array $dynamicShopSection
     * @return bool
     */
    public function hasMoreItems(array $dynamicShopSection): bool
    {
        return false;
    }
}
