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
        return $homePage->items()
            ->with('item')
            ->take(Pagination::PER_PAGE)->get()->map(function ($item) {
                $product = $item->item;
                if (!$product) {
                    return null;
                }

                return new ProductCardResource($product);
            })->filter()->toArray();
    }
}
