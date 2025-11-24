<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\HomePage\App\Models\HomePage;
use Modules\HomePage\App\Transformers\HomeBannerResource;
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
        return $homePage->items()->with('item')->limit(Pagination::PER_PAGE)->get()->map(function ($item) {
            return new HomeBannerResource($item);
        })->filter()->toArray();
    }
}
