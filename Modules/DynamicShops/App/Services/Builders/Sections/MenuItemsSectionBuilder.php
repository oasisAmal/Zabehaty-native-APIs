<?php

namespace Modules\DynamicShops\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\DynamicShops\App\Services\Builders\Concerns\UsesDynamicShopsQueryBuilder;
use Modules\DynamicShops\App\Services\Builders\Interfaces\SectionBuilderInterface;

class MenuItemsSectionBuilder implements SectionBuilderInterface
{
    use UsesDynamicShopsQueryBuilder;
    /**
     * Build menu items section data (supports products or shops).
     *
     * @param array $dynamicShopSection
     * @return array
     */
    public function build(array $dynamicShopSection): array
    {
        $titleColumn = app()->getLocale() === 'ar' ? 'title_ar' : 'title_en';

        $menuGroupIds = $this->getConnection()
            ->table('dynamic_shop_section_items')
            ->where('dynamic_shop_section_id', $dynamicShopSection['id'])
            ->selectRaw('MIN(id) as id')
            ->groupBy('menu_item_parent_id')
            ->pluck('id');

        return $this->getConnection()
            ->table('dynamic_shop_section_items')
            ->where('dynamic_shop_section_id', $dynamicShopSection['id'])
            ->whereIn('id', $menuGroupIds)
            ->select([
                'id',
                'menu_item_parent_id',
                'image_ar_url',
                'image_en_url',
            ])
            ->selectRaw("{$titleColumn} as title")
            ->get()
            ->map(function ($menuGroup) {
                return [
                    'id' => $menuGroup->menu_item_parent_id,
                    'name' => $menuGroup->title,
                    'image_url' => $this->getImageUrl($menuGroup),
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    public function hasMoreItems(array $dynamicShopSection): bool
    {
        return $this->getConnection()
            ->table('dynamic_shop_section_items')
            ->where('dynamic_shop_section_id', $dynamicShopSection['id'])
            ->count() > Pagination::PER_PAGE;
    }

    private function getImageUrl(object $item): string
    {
        if (request()->app_lang == 'ar') {
            return $item->image_ar_url ?? '';
        }
        return $item->image_en_url ?? '';
    }
}
