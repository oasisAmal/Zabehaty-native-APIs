<?php

namespace Modules\HomePage\App\Services\Builders;

use App\Models\HomeSection;
use Modules\HomePage\App\Services\Builders\Factories\SectionBuilderFactory;

class SectionBuilder
{
    protected SectionBuilderFactory $sectionBuilderFactory;

    public function __construct(SectionBuilderFactory $sectionBuilderFactory)
    {
        $this->sectionBuilderFactory = $sectionBuilderFactory;
    }

    /**
     * Build all active sections
     *
     * @return array
     */
    public function buildAll(): array
    {
        return []; //Until we have sections in the database

        $sections = HomeSection::active()
            ->ordered()
            ->get();

        return $sections->map(function ($section) {
            return $this->buildSection($section);
        })->toArray();
    }

    /**
     * Build a single section
     *
     * @param HomeSection $section
     * @return array
     */
    public function buildSection(HomeSection $section): array
    {
        $builder = $this->sectionBuilderFactory->create($section->type);
        
        return [
            'id' => $section->id,
            'type' => $section->type->value,
            'title' => $section->title,
            'title_image_url' => $section->title_image_url,
            'data' => $builder->build($section),
        ];
    }
}
