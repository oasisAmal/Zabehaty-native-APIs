<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Models\HomeSection;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class ShopSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build shop section data
     *
     * @param HomeSection $section
     * @return array
     */
    public function build(HomeSection $section): array
    {
        $shops = $section->getActiveShops();

        return $shops->map(function ($sectionShop) {
            $shop = $sectionShop->shop;

            if (!$shop) {
                return null;
            }

            return [
                'id' => $shop->id,
                'name' => $shop->name,
                'image_url' => $shop->image_url ?? null,
            ];
        })->filter()->toArray();
    }
}
