<?php

namespace Modules\DynamicShops\App\Services\Builders;

use Modules\DynamicShops\Enums\DynamicShopSectionType;
use Modules\DynamicShops\App\Queries\DynamicShopQuery;
use Modules\DynamicShops\App\Services\Builders\Factories\SectionBuilderFactory;

class SectionBuilder
{
    protected SectionBuilderFactory $sectionBuilderFactory;
    protected DynamicShopQuery $dynamicShopQuery;

    public function __construct(
        SectionBuilderFactory $sectionBuilderFactory,
        DynamicShopQuery $dynamicShopQuery
    )
    {
        $this->sectionBuilderFactory = $sectionBuilderFactory;
        $this->dynamicShopQuery = $dynamicShopQuery;
    }

    /**
     * Build all active sections for a shop
     *
     * @param int $shopId
     * @return array
     */
    public function buildAll(int $shopId): array
    {
        $dynamicShopSections = $this->dynamicShopQuery->fetchSections($shopId);

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

}
