<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use Modules\HomePage\App\Models\HomePage;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class BannerSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build banner section data
     *
     * @param HomePage $homePage
     * @return array
     */
    public function build(HomePage $homePage): array
    {
        return $homePage->items()->with('item')->get()->map(function ($item) {
            $banner = $item->item;

            if (!$banner) {
                return null;
            }

            // return new BannerCardResource($banner);
            return $banner;
        })->filter()->toArray();
    }
}
