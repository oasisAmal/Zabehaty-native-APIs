<?php

namespace Modules\HomePage\App\Services\Builders;

use Modules\HomePage\App\Models\HomePage;
use Modules\HomePage\Enums\HomeSectionType;
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
        $homePages = HomePage::ordered()
            ->whereHas('items')
            ->with('items')
            ->get();

        // Load all items and group by type for efficient loading
        $this->loadPolymorphicItems($homePages);

        return $homePages
            ->map(function ($homePage) {
                return $this->buildSection($homePage);
            })
            ->filter(function ($section) {
                return !empty($section['items']);
            })
            ->values()
            ->toArray();
    }

    /**
     * Efficiently load polymorphic items by type
     *
     * @param \Illuminate\Database\Eloquent\Collection $homePages
     * @return void
     */
    protected function loadPolymorphicItems($homePages): void
    {
        $itemsByType = [
            'Modules\\Products\\App\\Models\\Product' => [],
            'Modules\\Categories\\App\\Models\\Category' => [],
            'Modules\\Shops\\App\\Models\\Shop' => [],
        ];

        // Group item IDs by type
        foreach ($homePages as $homePage) {
            foreach ($homePage->items as $item) {
                if ($item->item_type && $item->item_id) {
                    $type = $item->item_type;
                    if (isset($itemsByType[$type])) {
                        $itemsByType[$type][$item->item_id] = $item;
                    }
                }
            }
        }

        // Load each type separately and map back
        foreach ($itemsByType as $type => $items) {
            if (empty($items)) {
                continue;
            }

            $ids = array_keys($items);
            $models = $this->loadModelsByType($type, $ids);

            // Map models back to items
            foreach ($models as $model) {
                if (isset($items[$model->id])) {
                    $items[$model->id]->setRelation('item', $model);
                }
            }
        }
    }

    /**
     * Load models by type
     *
     * @param string $type
     * @param array $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadModelsByType(string $type, array $ids): \Illuminate\Database\Eloquent\Collection
    {
        return match ($type) {
            'Modules\\Products\\App\\Models\\Product' => \Modules\Products\App\Models\Product::whereIn('id', $ids)->get()->keyBy('id'),
            'Modules\\Categories\\App\\Models\\Category' => \Modules\Categories\App\Models\Category::whereIn('id', $ids)->get()->keyBy('id'),
            'Modules\\Shops\\App\\Models\\Shop' => \Modules\Shops\App\Models\Shop::whereIn('id', $ids)->get()->keyBy('id'),
            default => collect(),
        };
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
