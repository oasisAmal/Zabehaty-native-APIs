<?php

namespace Modules\DynamicCategories\App\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\CountryQueryBuilderTrait;

class DynamicCategoryQuery
{
    use CountryQueryBuilderTrait;

    public function fetchSections(int $categoryId): Collection
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $imageLang = request()->app_lang === 'ar' ? 'ar' : $locale;

        $query = $this->getCountryConnection()
            ->table('dynamic_category_sections')
            ->select([
                'dynamic_category_sections.id',
                'dynamic_category_sections.type',
                'dynamic_category_sections.background_image_url',
                'dynamic_category_sections.display_type',
                'dynamic_category_sections.menu_type',
                'dynamic_category_sections.item_type',
                'dynamic_category_sections.banner_size',
                'dynamic_category_sections.sorting',
            ])
            ->selectRaw("dynamic_category_sections.title_{$locale} as title")
            ->selectRaw("dynamic_category_sections.title_image_{$imageLang}_url as title_image_url")
            ->where('dynamic_category_sections.category_id', $categoryId)
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('dynamic_category_section_items')
                    ->whereColumn('dynamic_category_section_items.dynamic_category_section_id', 'dynamic_category_sections.id');
            })
            ->orderBy('dynamic_category_sections.sorting');

        $this->applyAddressFilter($query);

        return $query->get();
    }

    private function applyAddressFilter($query): void
    {
        $user = auth('api')->user();
        if (! $user) {
            return;
        }

        $defaultAddress = $user->defaultAddress;
        if (! $defaultAddress) {
            return;
        }

        $query->whereJsonContains('dynamic_category_sections.emirate_ids', (string) $defaultAddress->emirate_id)
            ->where(function ($innerQuery) use ($defaultAddress) {
                $innerQuery->whereNull('dynamic_category_sections.region_ids')
                    ->orWhereJsonContains('dynamic_category_sections.region_ids', (string) $defaultAddress->region_id);
            });
    }
}
