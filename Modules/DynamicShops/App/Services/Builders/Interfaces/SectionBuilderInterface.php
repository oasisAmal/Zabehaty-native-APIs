<?php

namespace Modules\DynamicShops\App\Services\Builders\Interfaces;

interface SectionBuilderInterface
{
    /**
     * Build section data
     *
     * @param array $dynamicShopSection
     * @return array
     */
    public function build(array $dynamicShopSection): array;

    /**
     * Check if there are more items to load
     *
     * @param array $dynamicShopSection
     * @return bool
     */
    public function hasMoreItems(array $dynamicShopSection): bool;
}
