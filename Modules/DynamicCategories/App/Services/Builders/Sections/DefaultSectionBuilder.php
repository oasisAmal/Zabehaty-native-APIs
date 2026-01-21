<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;

class DefaultSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build default section data
     *
     * @param array $dynamicCategorySection
     * @return array
     */
    public function build(array $dynamicCategorySection): array
    {
        return [];
    }

    public function hasMoreItems(array $dynamicCategorySection): bool
    {
        return false;
    }
}

