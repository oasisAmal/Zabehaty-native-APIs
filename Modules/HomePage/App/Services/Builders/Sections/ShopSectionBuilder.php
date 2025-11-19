<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use Modules\HomePage\App\Models\HomePage;
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
        return $homePage->items()->with('item')->limit(10)->get()->map(function ($item) {
            $shop = $item->item;

            if (!$shop) {
                return null;
            }

            // return new ShopCardResource($shop);
            return $shop;
        })->filter()->toArray();
    }
}
