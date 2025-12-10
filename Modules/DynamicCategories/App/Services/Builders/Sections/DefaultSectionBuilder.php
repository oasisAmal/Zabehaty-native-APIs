<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use Modules\DynamicCategories\App\Models\DynamicCategorySection;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;

class DefaultSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build default section data
     *
     * @param DynamicCategorySection $dynamicCategorySection
     * @return array
     */
    public function build(DynamicCategorySection $dynamicCategorySection): array
    {
        return [];
    }
}

