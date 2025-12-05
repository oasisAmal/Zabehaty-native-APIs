<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\HomePage\App\Models\HomePage;
use Modules\Products\App\Transformers\ProductCardResource;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class ProductSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build product section data
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
                return new ProductCardResource($item->item);
            })
            ->values()
            ->toArray();
    }
}
