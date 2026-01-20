<?php

namespace Modules\HomePage\App\Services\Builders;

use Illuminate\Support\Facades\DB;
use Modules\HomePage\App\Services\Builders\Concerns\UsesHomepageQueryBuilder;
use Modules\HomePage\Enums\HomeSectionType;
use Modules\HomePage\App\Services\Builders\Factories\SectionBuilderFactory;

class SectionBuilder
{
    use UsesHomepageQueryBuilder;
    protected SectionBuilderFactory $sectionBuilderFactory;

    public function __construct(SectionBuilderFactory $sectionBuilderFactory)
    {
        $this->sectionBuilderFactory = $sectionBuilderFactory;
    }

    /**
     * Build all active sections
     *
     * @return array
     */
    public function buildAll(): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $imageLang = request()->app_lang === 'ar' ? 'ar' : $locale;

        $query = $this->getConnection()
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

        $this->applyHomePageAddressFilter($query);

        $homePages = $query->get();

        return $homePages
            ->map(function ($homePage) {
                return $this->buildSection((array) $homePage);
            })
            ->filter(fn($section) => !empty($section['items']))
            ->values()
            ->toArray();
    }

    /**
     * Build a single section
     *
     * @param array $homePage
     * @return array
     */
    public function buildSection(array $homePage): array
    {
        $builder = $this->sectionBuilderFactory->create($homePage['type']);

        $type = $homePage['type'];
        if ($type == HomeSectionType::LIMITED_TIME_OFFERS) {
            $type = HomeSectionType::PRODUCTS;
        }

        return [
            'id' => $homePage['id'],
            'type' => $type,
            'title' => $homePage['title'] ?? null,
            'title_image_url' => $homePage['title_image_url'] ?? null,
            'background_image_url' => $homePage['background_image_url'] ?? null,
            'banner_size' => $homePage['banner_size'] ?? '',
            'sorting' => $homePage['sorting'] ?? null,
            'items' => $builder->build($homePage),
        ];
    }

    private function applyHomePageAddressFilter($query): void
    {
        $defaultAddress = $this->getDefaultAddress();
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
