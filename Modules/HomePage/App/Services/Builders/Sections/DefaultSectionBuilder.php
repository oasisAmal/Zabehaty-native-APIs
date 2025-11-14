<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use Modules\HomePage\App\Models\HomePage;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class DefaultSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build default section data
     *
     * @param HomePage $homePage
     * @return array
     */
    public function build(HomePage $homePage): array
    {
        return [];
    }
}
