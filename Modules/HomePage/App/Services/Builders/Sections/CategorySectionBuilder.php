<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Models\HomeSection;
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
        $categories = $section->getActiveCategories();

        return $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image_url' => $category->image_url ?? null,
            ];
        })->toArray();
    }
}
