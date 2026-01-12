<?php

namespace Modules\DynamicShops\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\DynamicShops\App\Models\DynamicShopSection;
use Modules\DynamicShops\App\Transformers\DynamicShopMenuResource;
use Modules\DynamicShops\App\Services\Builders\Interfaces\SectionBuilderInterface;

class MenuItemsSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build menu items section data (supports products or shops).
     *
     * @param DynamicShopSection $dynamicShopSection
     * @return array
     */
    public function build(DynamicShopSection $dynamicShopSection): array
    {
        $menuGroupIds = $dynamicShopSection->items()
            ->selectRaw('MIN(id) as id')
            ->groupBy('menu_item_parent_id')
            ->pluck('id');

        return $dynamicShopSection->items()
            ->whereIn('id', $menuGroupIds)
            ->get()
            ->map(function ($menuGroup) {
                return new DynamicShopMenuResource($menuGroup);
            })
            ->filter()
            ->values()
            ->toArray();
    }

    public function hasMoreItems(DynamicShopSection $dynamicShopSection): bool
    {
        return $dynamicShopSection->items()->count() > Pagination::PER_PAGE;
    }
}
