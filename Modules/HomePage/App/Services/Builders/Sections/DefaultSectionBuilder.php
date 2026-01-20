<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class DefaultSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build default section data
     *
     * @param array $homePage
     * @return array
     */
    public function build(array $homePage): array
    {
        return [];
    }
}
