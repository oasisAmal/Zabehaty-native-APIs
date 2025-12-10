<?php

namespace Modules\DynamicCategories\App\Services\Builders\Interfaces;

use Modules\DynamicCategories\App\Models\DynamicCategorySection;

interface SectionBuilderInterface
{
    /**
     * Build section data
     *
     * @param DynamicCategorySection $dynamicCategorySection
     * @return array
     */
    public function build(DynamicCategorySection $dynamicCategorySection): array;
}

