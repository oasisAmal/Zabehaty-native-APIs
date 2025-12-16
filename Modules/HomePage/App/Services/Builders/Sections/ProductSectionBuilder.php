<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Illuminate\Support\Collection;
use Modules\Products\App\Models\Product;
use Modules\HomePage\App\Models\HomePage;
use Modules\Products\App\Transformers\ProductCardResource;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\Products\App\Models\Scopes\MatchedDefaultAddressScope as ProductMatchedDefaultAddressScope;

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
        return $this->resolveItems($homePage)
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
            Product::class => fn ($query) => $query->withoutGlobalScope(ProductMatchedDefaultAddressScope::class),
        ]);

        return $homePage->items;
    }
}
