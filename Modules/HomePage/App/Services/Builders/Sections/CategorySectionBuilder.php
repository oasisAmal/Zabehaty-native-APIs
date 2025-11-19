<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use Modules\HomePage\App\Models\HomePage;
use Modules\Categories\App\Transformers\CategoryCardResource;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class CategorySectionBuilder implements SectionBuilderInterface
{
    /**
     * Build category section data
     *
     * @param HomePage $homePage
     * @return array
     */
    public function build(HomePage $homePage): array
    {
        return $homePage->items()->with('item')->limit(10)->get()->map(function ($item) {
            $category = $item->item;
            if (!$category) {
                return null;
            }

            return new CategoryCardResource($category);
        })->filter()->toArray();
    }
}
