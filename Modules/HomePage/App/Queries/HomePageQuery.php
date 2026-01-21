<?php

namespace Modules\HomePage\App\Queries;

use Illuminate\Support\Facades\DB;
use App\Traits\CountryQueryBuilderTrait;
use Illuminate\Support\Collection;

class HomePageQuery
{
    use CountryQueryBuilderTrait;

    public function fetchSections(): Collection
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $imageLang = request()->app_lang === 'ar' ? 'ar' : $locale;

        $query = $this->getCountryConnection()
            ->table('home_page')
            ->select([
                'home_page.id',
                'home_page.type',
                'home_page.background_image_url',
                'home_page.banner_size',
                'home_page.sorting',
            ])
            ->selectRaw("home_page.title_{$locale} as title")
            ->selectRaw("home_page.title_image_{$imageLang}_url as title_image_url")
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('home_page_items')
                    ->whereColumn('home_page_items.home_page_id', 'home_page.id');
            })
            ->orderBy('home_page.sorting');

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

        $query->whereJsonContains('home_page.emirate_ids', (string) $defaultAddress->emirate_id)
            ->where(function ($innerQuery) use ($defaultAddress) {
                $innerQuery->whereNull('home_page.region_ids')
                    ->orWhereJsonContains('home_page.region_ids', (string) $defaultAddress->region_id);
            });
    }
}
