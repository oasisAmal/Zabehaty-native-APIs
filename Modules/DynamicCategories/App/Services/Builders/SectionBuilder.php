<?php

namespace Modules\DynamicCategories\App\Services\Builders;

use Modules\DynamicCategories\App\Queries\DynamicCategoryQuery;
use Modules\DynamicCategories\Enums\DynamicCategorySectionType;
use Modules\DynamicCategories\App\Services\Builders\Factories\SectionBuilderFactory;

class SectionBuilder
{
    protected SectionBuilderFactory $sectionBuilderFactory;
    protected DynamicCategoryQuery $dynamicCategoryQuery;

    public function __construct(
        SectionBuilderFactory $sectionBuilderFactory,
        DynamicCategoryQuery $dynamicCategoryQuery
    )
    {
        $this->sectionBuilderFactory = $sectionBuilderFactory;
        $this->dynamicCategoryQuery = $dynamicCategoryQuery;
    }

    /**
     * Build all active sections for a category
     *
     * @param int $categoryId
     * @return array
     */
    public function buildAll(int $categoryId): array
    {
        $dynamicCategorySections = $this->dynamicCategoryQuery->fetchSections($categoryId);

        return $dynamicCategorySections
            ->map(function ($dynamicCategorySection) {
                return $this->buildSection((array) $dynamicCategorySection);
            })
            ->filter(fn($section) => !empty($section['items']))
            ->values()
            ->toArray();
    }

    /**
     * Build a single section
     *
     * @param array $dynamicCategorySection
     * @return array
     */
    public function buildSection(array $dynamicCategorySection): array
    {
        $builder = $this->sectionBuilderFactory->create($dynamicCategorySection['type']);

        $type = $dynamicCategorySection['type'];
        if ($type == DynamicCategorySectionType::LIMITED_TIME_OFFERS) {
            $type = DynamicCategorySectionType::PRODUCTS;
        }

        return [
            'id' => $dynamicCategorySection['id'],
            'type' => $type,
            'title' => $dynamicCategorySection['title'] ?? null,
            'title_image_url' => $dynamicCategorySection['title_image_url'] ?? null,
            'background_image_url' => $dynamicCategorySection['background_image_url'] ?? null,
            'display_type' => $dynamicCategorySection['display_type'] ? strtolower($dynamicCategorySection['display_type']) : null,
            'menu_type' => $dynamicCategorySection['menu_type'] ? strtolower($dynamicCategorySection['menu_type']) : null,
            'item_type' => $dynamicCategorySection['item_type'] ? strtolower($dynamicCategorySection['item_type']) : null,
            'banner_size' => $dynamicCategorySection['banner_size'] ? strtolower($dynamicCategorySection['banner_size']) : null,
            'sorting' => $dynamicCategorySection['sorting'] ?? null,
            'has_more_items' => $builder->hasMoreItems($dynamicCategorySection),
            'items' => $builder->build($dynamicCategorySection),
        ];
    }

}

