<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Illuminate\Support\Collection;
use Modules\Products\App\Models\Product;
use Modules\DynamicCategories\App\Models\DynamicCategorySection;
use Modules\Products\App\Transformers\ProductCardResource;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\Products\App\Models\Scopes\MatchedDefaultAddressScope as ProductMatchedDefaultAddressScope;

class ProductSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build product section data
     *
     * @param DynamicCategorySection $dynamicCategorySection
     * @return array
     */
    public function build(DynamicCategorySection $dynamicCategorySection): array
    {
        return $this->resolveItems($dynamicCategorySection)
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

    /**
     * Ensure items are loaded without the costly visibility scope.
     */
    private function resolveItems(DynamicCategorySection $dynamicCategorySection): Collection
    {
        if ($dynamicCategorySection->relationLoaded('items')) {
            $items = $dynamicCategorySection->items;

            if ($items->isEmpty() || $items->first()->relationLoaded('item')) {
                return $items;
            }
        }

        $dynamicCategorySection->load('items');

        $dynamicCategorySection->loadMorph('items.item', [
            Product::class => fn($query) => $query->withoutGlobalScope(ProductMatchedDefaultAddressScope::class),
        ]);

        return $dynamicCategorySection->items;
    }

    public function hasMoreItems(DynamicCategorySection $dynamicCategorySection): bool
    {
        return $this->resolveItems($dynamicCategorySection)->filter(function ($item) {
            return $item->item !== null;
        })->count() > Pagination::PER_PAGE;
    }
}
