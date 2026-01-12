<?php

namespace Modules\DynamicShops\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Illuminate\Support\Collection;
use Modules\Shops\App\Models\Shop;
use Modules\DynamicShops\App\Models\DynamicShopSection;
use Modules\Shops\App\Transformers\ShopCardResource;
use Modules\DynamicShops\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\Shops\App\Models\Scopes\MatchedDefaultAddressScope as ShopMatchedDefaultAddressScope;

class ShopSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build shop section data
     *
     * @param DynamicShopSection $dynamicShopSection
     * @return array
     */
    public function build(DynamicShopSection $dynamicShopSection): array
    {
        return $this->resolveItems($dynamicShopSection)
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
    private function resolveItems(DynamicShopSection $dynamicShopSection): Collection
    {
        if ($dynamicShopSection->relationLoaded('items')) {
            $items = $dynamicShopSection->items;

            if ($items->isEmpty() || $items->first()->relationLoaded('item')) {
                return $items;
            }
        }

        $dynamicShopSection->load('items');

        $dynamicShopSection->loadMorph('items.item', [
            Shop::class => fn ($query) => $query->withoutGlobalScope(ShopMatchedDefaultAddressScope::class),
        ]);

        return $dynamicShopSection->items;
    }

    public function hasMoreItems(DynamicShopSection $dynamicShopSection): bool
    {
        return $this->resolveItems($dynamicShopSection)->filter(function ($item) {
            return $item->item !== null;
        })->count() > 20;
    }
}
