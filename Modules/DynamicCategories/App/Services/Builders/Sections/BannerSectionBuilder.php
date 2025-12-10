<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\DynamicCategories\App\Models\DynamicCategorySection;
use Modules\DynamicCategories\App\Transformers\DynamicCategoryBannerResource;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;

class BannerSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build banner section data
     *
     * @param DynamicCategorySection $dynamicCategorySection
     * @return array
     */
    public function build(DynamicCategorySection $dynamicCategorySection): array
    {
        return $dynamicCategorySection->items()->with('item')->limit(Pagination::PER_PAGE)->get()->map(function ($item) {
            return new DynamicCategoryBannerResource($item);
        })->filter()->toArray();
    }
}

