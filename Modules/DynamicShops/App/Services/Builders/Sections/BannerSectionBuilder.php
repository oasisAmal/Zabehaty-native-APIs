<?php

namespace Modules\DynamicShops\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\DynamicShops\App\Models\DynamicShopSection;
use Modules\DynamicShops\App\Transformers\DynamicShopBannerResource;
use Modules\DynamicShops\App\Services\Builders\Interfaces\SectionBuilderInterface;

class BannerSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build banner section data
     *
     * @param DynamicShopSection $dynamicShopSection
     * @return array
     */
    public function build(DynamicShopSection $dynamicShopSection): array
    {
        return $dynamicShopSection->items()->with('item')->limit(Pagination::PER_PAGE)->get()->map(function ($item) {
            return new DynamicShopBannerResource($item);
        })->filter()->toArray();
    }

    public function hasMoreItems(DynamicShopSection $dynamicShopSection): bool
    {
        return $dynamicShopSection->items()->count() > Pagination::PER_PAGE;
    }
}
