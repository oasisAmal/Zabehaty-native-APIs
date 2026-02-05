<?php

namespace Modules\Search\App\Services\Builders\Interfaces;

interface SectionBuilderInterface
{
    /**
     * Build section data
     *
     * @return array
     */
    public function build(): array;
}
