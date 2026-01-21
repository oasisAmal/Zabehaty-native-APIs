<?php

namespace Modules\HomePage\App\Services\Builders;

use Modules\HomePage\App\Queries\HomePageQuery;
use Modules\HomePage\Enums\HomeSectionType;
use Modules\HomePage\App\Services\Builders\Factories\SectionBuilderFactory;

class SectionBuilder
{
    protected SectionBuilderFactory $sectionBuilderFactory;
    protected HomePageQuery $homePageQuery;

    public function __construct(
        SectionBuilderFactory $sectionBuilderFactory,
        HomePageQuery $homePageQuery
    )
    {
        $this->sectionBuilderFactory = $sectionBuilderFactory;
        $this->homePageQuery = $homePageQuery;
    }

    /**
     * Build all active sections
     *
     * @return array
     */
    public function buildAll(): array
    {
        $homePages = $this->homePageQuery->fetchSections();

        return $homePages
            ->map(function ($homePage) {
                return $this->buildSection((array) $homePage);
            })
            ->filter(fn($section) => !empty($section['items']))
            ->values()
            ->toArray();
    }

    /**
     * Build a single section
     *
     * @param array $homePage
     * @return array
     */
    public function buildSection(array $homePage): array
    {
        $builder = $this->sectionBuilderFactory->create($homePage['type']);

        $type = $homePage['type'];
        if ($type == HomeSectionType::LIMITED_TIME_OFFERS) {
            $type = HomeSectionType::PRODUCTS;
        }

        return [
            'id' => $homePage['id'],
            'type' => $type,
            'title' => $homePage['title'] ?? null,
            'title_image_url' => $homePage['title_image_url'] ?? null,
            'background_image_url' => $homePage['background_image_url'] ?? null,
            'banner_size' => $homePage['banner_size'] ?? '',
            'sorting' => $homePage['sorting'] ?? null,
            'items' => $builder->build($homePage),
        ];
    }

}
