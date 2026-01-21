<?php

namespace Modules\DynamicShops\App\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\CountryQueryBuilderTrait;

class DynamicShopQuery
{
    use CountryQueryBuilderTrait;

    public function fetchSections(int $shopId): Collection
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $imageLang = request()->app_lang === 'ar' ? 'ar' : $locale;

        $query = $this->getCountryConnection()
            ->table('dynamic_shop_sections')
            ->select([
                'dynamic_shop_sections.id',
                'dynamic_shop_sections.type',
                'dynamic_shop_sections.background_image_url',
                'dynamic_shop_sections.display_type',
                'dynamic_shop_sections.menu_type',
                'dynamic_shop_sections.item_type',
                'dynamic_shop_sections.banner_size',
                'dynamic_shop_sections.sorting',
            ])
            ->selectRaw("dynamic_shop_sections.title_{$locale} as title")
            ->selectRaw("dynamic_shop_sections.title_image_{$imageLang}_url as title_image_url")
            ->where('dynamic_shop_sections.shop_id', $shopId)
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('dynamic_shop_section_items')
                    ->whereColumn('dynamic_shop_section_items.dynamic_shop_section_id', 'dynamic_shop_sections.id');
            })
            ->orderBy('dynamic_shop_sections.sorting');

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

        $query->whereJsonContains('dynamic_shop_sections.emirate_ids', (string) $defaultAddress->emirate_id)
            ->where(function ($innerQuery) use ($defaultAddress) {
                $innerQuery->whereNull('dynamic_shop_sections.region_ids')
                    ->orWhereJsonContains('dynamic_shop_sections.region_ids', (string) $defaultAddress->region_id);
            });
    }
}
