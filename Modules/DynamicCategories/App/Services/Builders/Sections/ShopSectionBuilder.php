<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Illuminate\Support\Collection;
use Modules\Shops\App\Models\Shop;
use Modules\DynamicCategories\App\Models\DynamicCategorySection;
use Modules\Shops\App\Transformers\ShopCardResource;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\Shops\App\Models\Scopes\MatchedDefaultAddressScope as ShopMatchedDefaultAddressScope;

class ShopSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build shop section data
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
            ->take(20)
            ->map(function ($item) {
                return new ShopCardResource($item->item);
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
            Shop::class => fn ($query) => $query->withoutGlobalScope(ShopMatchedDefaultAddressScope::class),
        ]);

        return $dynamicCategorySection->items;
    }

    public function hasMoreItems(DynamicCategorySection $dynamicCategorySection): bool
    {
        return $this->resolveItems($dynamicCategorySection)->filter(function ($item) {
            return $item->item !== null;
        })->count() > 20;
    }
}

