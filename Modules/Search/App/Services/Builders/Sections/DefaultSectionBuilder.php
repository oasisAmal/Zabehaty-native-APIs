<?php

namespace Modules\Search\App\Services\Builders\Sections;

use Modules\Search\App\Services\Builders\Interfaces\SectionBuilderInterface;

class DefaultSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build default section data
     *
     * @return array
     */
    public function build(): array
    {
        return [];
    }
}
