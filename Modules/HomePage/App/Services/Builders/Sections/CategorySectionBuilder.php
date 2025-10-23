<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Models\HomeSection;
use App\Models\MainCategory;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class CategorySectionBuilder implements SectionBuilderInterface
{
    /**
     * Build category section data
     *
     * @param HomeSection $section
     * @return array
     */
    public function build(HomeSection $section): array
    {
        // Get categories based on section settings or all active categories
        $limit = $section->settings['limit'] ?? 10;
        
        $categories = MainCategory::active()
            ->ordered()
            ->limit($limit)
            ->get();

        return [
            'categories' => $categories->map(function ($category) {
                return [
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'icon_url' => $category->icon_path,
                ];
            })->toArray(),
            'settings' => $section->settings ?? [],
        ];
    }
}
