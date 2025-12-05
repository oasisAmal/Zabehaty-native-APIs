<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Enums\Pagination;
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
        $items = $homePage->relationLoaded('items')
            ? $homePage->items
            : $homePage->items()->with('item')->get();

        return $items
            ->filter(function ($item) {
                return $item->item !== null;
            })
            ->take(Pagination::PER_PAGE)
            ->map(function ($item) {
                return new CategoryCardResource($item->item);
            })
            ->values()
            ->toArray();
    }
}
