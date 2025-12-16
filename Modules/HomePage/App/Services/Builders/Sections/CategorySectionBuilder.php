<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Illuminate\Support\Collection;
use Modules\Categories\App\Models\Category;
use Modules\HomePage\App\Models\HomePage;
use Modules\Categories\App\Transformers\CategoryCardResource;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\Categories\App\Models\Scopes\MatchedDefaultAddressScope as CategoryMatchedDefaultAddressScope;

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
        return $this->resolveItems($homePage)
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
            Category::class => fn ($query) => $query->withoutGlobalScope(CategoryMatchedDefaultAddressScope::class),
        ]);

        return $homePage->items;
    }
}
