<?php

namespace Modules\HomePage\App\Services\Builders\Factories;

use Modules\HomePage\Enums\HomeSectionType;
use Modules\HomePage\App\Services\Builders\Sections\BannerSectionBuilder;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\HomePage\App\Services\Builders\Sections\CategorySectionBuilder;
use Modules\HomePage\App\Services\Builders\Sections\DefaultSectionBuilder;
use Modules\HomePage\App\Services\Builders\Sections\FeaturedSectionBuilder;
use Modules\HomePage\App\Services\Builders\Sections\ProductSectionBuilder;

class SectionBuilderFactory
{
    /**
     * Create appropriate section builder based on type
     *
     * @param HomeSectionType $type
     * @return SectionBuilderInterface
     */
    public function create(HomeSectionType $type): SectionBuilderInterface
    {
        return match ($type) {
            HomeSectionType::BANNER => new BannerSectionBuilder(),
            HomeSectionType::BANNERS => new BannerSectionBuilder(),
            HomeSectionType::PRODUCTS => new ProductSectionBuilder(),
            HomeSectionType::CATEGORIES => new CategorySectionBuilder(),
            HomeSectionType::FEATURED => new FeaturedSectionBuilder(),
            HomeSectionType::OFFERS => new ProductSectionBuilder(),
            HomeSectionType::NEW_ARRIVALS => new ProductSectionBuilder(),
            HomeSectionType::BEST_SELLERS => new ProductSectionBuilder(),
            default => new DefaultSectionBuilder(),
        };
    }
}
