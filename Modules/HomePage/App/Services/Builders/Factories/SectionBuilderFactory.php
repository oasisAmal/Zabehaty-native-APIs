<?php

namespace Modules\HomePage\App\Services\Builders\Factories;

use Modules\HomePage\Enums\HomeSectionType;
use Modules\HomePage\App\Services\Builders\Sections\BannerSectionBuilder;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\HomePage\App\Services\Builders\Sections\CategorySectionBuilder;
use Modules\HomePage\App\Services\Builders\Sections\DefaultSectionBuilder;
use Modules\HomePage\App\Services\Builders\Sections\ProductSectionBuilder;
use Modules\HomePage\App\Services\Builders\Sections\ShopSectionBuilder;

class SectionBuilderFactory
{
    /**
     * Create appropriate section builder based on type
     *
     * @param string $type
     * @return SectionBuilderInterface
     */
    public function create($type): SectionBuilderInterface
    {
        return match ($type) {
            HomeSectionType::BANNERS => new BannerSectionBuilder(),
            HomeSectionType::SHOPS => new ShopSectionBuilder(),
            HomeSectionType::CATEGORIES => new CategorySectionBuilder(),
            HomeSectionType::LIMITED_TIME_OFFERS,
            HomeSectionType::PRODUCTS => new ProductSectionBuilder(),
            default => new DefaultSectionBuilder(),
        };
    }
}
