<?php

namespace Modules\Search\App\Services\Builders;

use Modules\Search\App\Services\Builders\Factories\SectionBuilderFactory;

class SectionBuilder
{
    protected SectionBuilderFactory $sectionBuilderFactory;

    public function __construct(SectionBuilderFactory $sectionBuilderFactory)
    {
        $this->sectionBuilderFactory = $sectionBuilderFactory;
    }

    public function buildSection(string $section): array
    {
        return $this->sectionBuilderFactory->create($section)->build();
    }
}
