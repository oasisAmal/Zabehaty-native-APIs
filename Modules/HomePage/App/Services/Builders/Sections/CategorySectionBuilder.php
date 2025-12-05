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
        return $homePage->items()
            // ->has('item')
            ->with('item')
            ->limit(Pagination::PER_PAGE)
            ->get()
            ->map(function ($item) {
                if(!$item->item) {
                    return null;
                }
                return new CategoryCardResource($item->item);
            })
            ->filter()
            // ->values()
            ->toArray();
    }
}
