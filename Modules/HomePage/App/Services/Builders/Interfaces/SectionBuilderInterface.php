<?php

namespace Modules\HomePage\App\Services\Builders\Interfaces;

use Modules\HomePage\App\Models\HomePage;

interface SectionBuilderInterface
{
    /**
     * Build section data
     *
     * @param HomePage $homePage
     * @return array
     */
    public function build(HomePage $homePage): array;
}
