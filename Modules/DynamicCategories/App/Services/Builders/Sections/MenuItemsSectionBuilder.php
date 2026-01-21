<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\DynamicCategories\App\Services\Builders\Concerns\UsesDynamicCategoriesQueryBuilder;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;

class MenuItemsSectionBuilder implements SectionBuilderInterface
{
    use UsesDynamicCategoriesQueryBuilder;
    const PER_PAGE = 20;
    
    /**
     * Build menu items section data (supports products or shops).
     *
     * @param array $dynamicCategorySection
     * @return array
     */
    public function build(array $dynamicCategorySection): array
    {
        $titleColumn = app()->getLocale() === 'ar' ? 'title_ar' : 'title_en';

        $menuGroupIds = $this->getConnection()
            ->table('dynamic_category_section_items')
            ->where('dynamic_category_section_id', $dynamicCategorySection['id'])
            ->selectRaw('MIN(id) as id')
            ->groupBy('menu_item_parent_id')
            ->pluck('id');

        return $this->getConnection()
            ->table('dynamic_category_section_items')
            ->where('dynamic_category_section_id', $dynamicCategorySection['id'])
            ->whereIn('id', $menuGroupIds)
            ->select([
                'id',
                'menu_item_parent_id',
                'image_ar_url',
                'image_en_url',
            ])
            ->selectRaw("{$titleColumn} as title")
            ->limit(self::PER_PAGE)
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

    public function hasMoreItems(array $dynamicCategorySection): bool
    {
        return $this->getConnection()
            ->table('dynamic_category_section_items')
            ->where('dynamic_category_section_id', $dynamicCategorySection['id'])
            ->count() > self::PER_PAGE;
    }

    private function getImageUrl(object $item): string
    {
        if (request()->app_lang == 'ar') {
            return $item->image_ar_url ?? '';
        }
        return $item->image_en_url ?? '';
    }
}
