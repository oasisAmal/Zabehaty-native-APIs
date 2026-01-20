<?php

namespace Modules\HomePage\App\Services\Builders\Interfaces;

interface SectionBuilderInterface
{
    /**
     * Build section data
     *
     * @param array $homePage
     * @return array
     */
    public function build(array $homePage): array;
}
