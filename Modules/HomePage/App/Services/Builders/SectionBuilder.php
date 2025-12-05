<?php

namespace Modules\HomePage\App\Services\Builders;

use Modules\HomePage\App\Models\HomePage;
use Modules\HomePage\Enums\HomeSectionType;
use Modules\HomePage\App\Services\Builders\Factories\SectionBuilderFactory;
use Modules\Products\App\Models\Scopes\MatchedDefaultAddressScope as ProductMatchedDefaultAddressScope;
use Modules\Categories\App\Models\Scopes\MatchedDefaultAddressScope as CategoryMatchedDefaultAddressScope;
use Modules\Shops\App\Models\Scopes\MatchedDefaultAddressScope as ShopMatchedDefaultAddressScope;

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
        return HomePage::ordered()
            ->has('items')
            // ->with(['items' => function ($query) {
            //     $query->where(function ($q) {
            //         $q->whereHas('item', function ($q2) {
            //             switch ($q2->getModel()->getTable()) {
            //                 case 'products':
            //                     return $q2->withoutGlobalScope(ProductMatchedDefaultAddressScope::class);
            //                 case 'categories':
            //                     return $q2->withoutGlobalScope(CategoryMatchedDefaultAddressScope::class);
            //                 case 'shops':
            //                     return $q2->withoutGlobalScope(ShopMatchedDefaultAddressScope::class);
            //             }
            //             return $q2;
            //         })->orWhereNotNull('external_link'); // also include items with external_link
            //     });
            // }, 'items.item'])
            ->with('items.item')
            ->get()
            ->map(function ($homePage) {
                return $this->buildSection($homePage);
            })
            ->filter(fn($section) => !empty($section['items']))
            ->values()
            ->toArray();
    }

    /**
     * Build a single section
     *
     * @param HomePage $homePage
     * @return array
     */
    public function buildSection(HomePage $homePage): array
    {
        $builder = $this->sectionBuilderFactory->create($homePage->type);

        $type = $homePage->type;
        if ($type == HomeSectionType::LIMITED_TIME_OFFERS) {
            $type = HomeSectionType::PRODUCTS;
        }

        return [
            'id' => $homePage->id,
            'type' => $type,
            'title' => $homePage->title,
            'title_image_url' => $homePage->title_image_url,
            'background_image_url' => $homePage->background_image_url,
            'banner_size' => $homePage->banner_size ?? '',
            'sorting' => $homePage->sorting,
            'items' => $builder->build($homePage),
        ];
    }
}
