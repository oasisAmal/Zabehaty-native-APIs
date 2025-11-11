<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Models\HomeSection;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class BannerSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build banner section data
     *
     * @param HomeSection $section
     * @return array
     */
    public function build(HomeSection $section): array
    {
        $banners = $section->getActiveBanners();

        return $banners->map(function ($banner) {
            return [
                'id' => $banner->id,
                'image_url' => $banner->full_image_url,
                'link' => $banner->link,
            ];
        })->toArray();
    }
}
