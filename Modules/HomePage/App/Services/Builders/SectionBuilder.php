<?php

namespace Modules\HomePage\App\Services\Builders;

use Modules\HomePage\App\Models\HomePage;
use Modules\HomePage\Enums\HomeSectionType;
use Modules\HomePage\App\Services\Builders\Factories\SectionBuilderFactory;
use Modules\Products\App\Models\Product;
use Modules\Shops\App\Models\Shop;
use Modules\Categories\App\Models\Category;
use Modules\Products\App\Models\Scopes\MatchedDefaultAddressScope as ProductMatchedDefaultAddressScope;
use Modules\Shops\App\Models\Scopes\MatchedDefaultAddressScope as ShopMatchedDefaultAddressScope;
use Modules\Categories\App\Models\Scopes\MatchedDefaultAddressScope as CategoryMatchedDefaultAddressScope;

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
        $homePages = HomePage::ordered()
            ->has('items')
            ->with('items')
            ->get();

        $homePages->loadMorph('items.item', [
            Product::class => fn ($query) => $query->withoutGlobalScope(ProductMatchedDefaultAddressScope::class),
            Shop::class => fn ($query) => $query->withoutGlobalScope(ShopMatchedDefaultAddressScope::class),
            Category::class => fn ($query) => $query->withoutGlobalScope(CategoryMatchedDefaultAddressScope::class),
        ]);

        return $homePages
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
