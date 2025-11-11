<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Models\HomeSection;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class DefaultSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build default section data
     *
     * @param HomeSection $section
     * @return array
     */
    public function build(HomeSection $section): array
    {
        return [];
    }
}
