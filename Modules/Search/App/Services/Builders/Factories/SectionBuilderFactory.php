<?php

namespace Modules\Search\App\Services\Builders\Factories;

use Modules\Search\Enums\SearchSectionType;
use Modules\Search\App\Services\Builders\Sections\BannerSectionBuilder;
use Modules\Search\App\Services\Builders\Sections\DefaultSectionBuilder;
use Modules\Search\App\Services\Builders\Interfaces\SectionBuilderInterface;
use Modules\Search\App\Services\Builders\Sections\RecentSearchWordsSectionBuilder;
use Modules\Search\App\Services\Builders\Sections\RecentlyViewedProductsSectionBuilder;

class SectionBuilderFactory
{
    /**
     * Create appropriate section builder based on section type
     *
     * @param string $section
     * @return SectionBuilderInterface
     */
    public function create(string $section): SectionBuilderInterface
    {
        return match ($section) {
            SearchSectionType::RECENT_SEARCH_WORDS => new RecentSearchWordsSectionBuilder(),
            SearchSectionType::BANNERS => new BannerSectionBuilder(),
            SearchSectionType::RECENTLY_VIEWED_PRODUCTS => new RecentlyViewedProductsSectionBuilder(),
            default => new DefaultSectionBuilder(),
        };
    }
}
