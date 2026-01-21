<?php

namespace Modules\DynamicCategories\App\Services\Builders\Interfaces;

interface SectionBuilderInterface
{
    /**
     * Build section data
     *
     * @param array $dynamicCategorySection
     * @return array
     */
    public function build(array $dynamicCategorySection): array;

    /**
     * Check if there are more items to load
     *
     * @param array $dynamicCategorySection
     * @return bool
     */
    public function hasMoreItems(array $dynamicCategorySection): bool;
}

