<?php

namespace Modules\DynamicShops\App\Services\Builders;

use Illuminate\Support\Facades\DB;
use Modules\DynamicShops\Enums\DynamicShopSectionType;
use Modules\DynamicShops\App\Services\Builders\Factories\SectionBuilderFactory;
use Modules\DynamicShops\App\Services\Builders\Concerns\UsesDynamicShopsQueryBuilder;

class SectionBuilder
{
    use UsesDynamicShopsQueryBuilder;
    protected SectionBuilderFactory $sectionBuilderFactory;

    public function __construct(SectionBuilderFactory $sectionBuilderFactory)
    {
        $this->sectionBuilderFactory = $sectionBuilderFactory;
    }

    /**
     * Build all active sections for a shop
     *
     * @param int $shopId
     * @return array
     */
    public function buildAll(int $shopId): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $imageLang = request()->app_lang === 'ar' ? 'ar' : $locale;

        $query = $this->getConnection()
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

        $this->applySectionAddressFilter($query);

        $dynamicShopSections = $query->get();

        return $dynamicShopSections
            ->map(function ($dynamicShopSection) {
                return $this->buildSection((array) $dynamicShopSection);
            })
            ->filter(fn($section) => !empty($section['items']))
            ->values()
            ->toArray();
    }

    /**
     * Build a single section
     *
     * @param array $dynamicShopSection
     * @return array
     */
    public function buildSection(array $dynamicShopSection): array
    {
        $builder = $this->sectionBuilderFactory->create($dynamicShopSection['type']);

        $type = $dynamicShopSection['type'];
        if ($type == DynamicShopSectionType::LIMITED_TIME_OFFERS) {
            $type = DynamicShopSectionType::PRODUCTS;
        }

        return [
            'id' => $dynamicShopSection['id'],
            'type' => $type,
            'title' => $dynamicShopSection['title'] ?? null,
            'title_image_url' => $dynamicShopSection['title_image_url'] ?? null,
            'background_image_url' => $dynamicShopSection['background_image_url'] ?? null,
            'display_type' => $dynamicShopSection['display_type'] ? strtolower($dynamicShopSection['display_type']) : null,
            'menu_type' => $dynamicShopSection['menu_type'] ? strtolower($dynamicShopSection['menu_type']) : null,
            'item_type' => $dynamicShopSection['item_type'] ? strtolower($dynamicShopSection['item_type']) : null,
            'banner_size' => $dynamicShopSection['banner_size'] ? strtolower($dynamicShopSection['banner_size']) : null,
            'sorting' => $dynamicShopSection['sorting'] ?? null,
            'has_more_items' => $builder->hasMoreItems($dynamicShopSection),
            'items' => $builder->build($dynamicShopSection),
        ];
    }

    private function applySectionAddressFilter($query): void
    {
        $defaultAddress = $this->getDefaultAddress();
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
