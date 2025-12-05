<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\HomePage\App\Models\HomePage;
use Modules\Shops\App\Transformers\ShopCardResource;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

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
        return $homePage->items()
            ->has('item')
            ->with('item')
            ->limit(Pagination::PER_PAGE)
            ->get()
            ->map(function ($item) {
                return new ShopCardResource($item->item);
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
