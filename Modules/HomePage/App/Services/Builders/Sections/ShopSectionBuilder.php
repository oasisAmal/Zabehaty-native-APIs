<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Illuminate\Support\Collection;
use Modules\Shops\App\Models\Shop;
use Illuminate\Support\Facades\Log;
use Modules\HomePage\App\Models\HomePage;
use Modules\Shops\App\Transformers\ShopCardResource;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\Shops\App\Models\Scopes\MatchedDefaultAddressScope as ShopMatchedDefaultAddressScope;

class ShopSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build shop section data
     *
     * @param HomePage $homePage
     * @return array
     */
    public function build(HomePage $homePage): array
    {
        return $this->resolveItems($homePage)
            ->filter(function ($item) {
                return $item->item !== null;
            })
            ->take(Pagination::PER_PAGE)
            ->map(function ($item) {
                return new ShopCardResource($item->item);
            })
            ->values()
            ->toArray();
    }

    /**
     * Ensure items are loaded without the costly visibility scope.
     */
    private function resolveItems(HomePage $homePage): Collection
    {
        if ($homePage->relationLoaded('items')) {
            $items = $homePage->items;

            if ($items->isEmpty() || $items->first()->relationLoaded('item')) {
                return $items;
            }
        }

        $homePage->load('items');

        $homePage->loadMorph('items.item', [
            Shop::class => fn ($query) => $query->withoutGlobalScope(ShopMatchedDefaultAddressScope::class),
        ]);

        return $homePage->items;
    }
}
