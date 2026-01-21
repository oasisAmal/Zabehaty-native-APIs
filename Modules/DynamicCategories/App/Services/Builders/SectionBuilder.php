<?php

namespace Modules\DynamicCategories\App\Services\Builders;

use Illuminate\Support\Facades\DB;
use Modules\DynamicCategories\Enums\DynamicCategorySectionType;
use Modules\DynamicCategories\App\Services\Builders\Factories\SectionBuilderFactory;
use Modules\DynamicCategories\App\Services\Builders\Concerns\UsesDynamicCategoriesQueryBuilder;

class SectionBuilder
{
    use UsesDynamicCategoriesQueryBuilder;
    protected SectionBuilderFactory $sectionBuilderFactory;

    public function __construct(SectionBuilderFactory $sectionBuilderFactory)
    {
        $this->sectionBuilderFactory = $sectionBuilderFactory;
    }

    /**
     * Build all active sections for a category
     *
     * @param int $categoryId
     * @return array
     */
    public function buildAll(int $categoryId): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $imageLang = request()->app_lang === 'ar' ? 'ar' : $locale;

        $query = $this->getConnection()
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

        $this->applySectionAddressFilter($query);

        $dynamicCategorySections = $query->get();

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

    private function applySectionAddressFilter($query): void
    {
        $defaultAddress = $this->getDefaultAddress();
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

