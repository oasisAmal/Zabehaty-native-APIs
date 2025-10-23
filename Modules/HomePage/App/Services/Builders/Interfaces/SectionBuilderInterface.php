<?php

namespace Modules\HomePage\App\Services\Builders\Interfaces;

use App\Models\HomeSection;

interface SectionBuilderInterface
{
    /**
     * Build section data
     *
     * @param HomeSection $section
     * @return array
     */
    public function build(HomeSection $section): array;
}
