<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use Modules\DynamicCategories\App\Models\DynamicCategorySection;
use Modules\DynamicCategories\App\Transformers\DynamicCategoryMenuResource;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;

class MenuItemsSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build menu items section data (supports products or shops).
     *
     * @param DynamicCategorySection $dynamicCategorySection
     * @return array
     */
    public function build(DynamicCategorySection $dynamicCategorySection): array
    {
        $menuGroupIds = $dynamicCategorySection->items()
            ->selectRaw('MIN(id) as id')
            ->groupBy('menu_item_parent_id')
            ->pluck('id');

        return $dynamicCategorySection->items()
            ->whereIn('id', $menuGroupIds)
            ->get()
            ->map(function ($menuGroup) {
                return new DynamicCategoryMenuResource($menuGroup);
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
