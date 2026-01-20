<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Illuminate\Support\Facades\DB;
use Modules\Categories\App\Models\Category;
use Modules\HomePage\App\Services\Builders\Concerns\UsesHomepageQueryBuilder;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class CategorySectionBuilder implements SectionBuilderInterface
{
    use UsesHomepageQueryBuilder;
    /**
     * Build category section data
     *
     * @param array $homePage
     * @return array
     */
    public function build(array $homePage): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $nameColumn = $locale === 'ar' ? 'name' : 'name_en';

        $query = $this->getConnection()
            ->table('home_page_items')
            ->join('categories', function ($join) {
                $join->on('categories.id', '=', 'home_page_items.item_id')
                    ->where('home_page_items.item_type', Category::class);
            })
            ->select([
                'categories.id',
                'categories.icon as image_url',
            ])
            ->selectRaw("categories.{$nameColumn} as name")
            ->where('home_page_items.home_page_id', $homePage['id'])
            ->where('categories.is_active', true)
            ->orderBy('home_page_items.id')
            ->limit(Pagination::PER_PAGE);

        $this->applyCategoryVisibility($query);

        $items = $query->get();

        return $items
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image_url' => $item->image_url,
                ];
            })
            ->toArray();
    }

    private function applyCategoryVisibility($query): void
    {
        $defaultAddress = $this->getDefaultAddress();
        if (! $defaultAddress) {
            return;
        }

        $this->applyVisibilityExists(
            $query,
            'category_visibilities',
            'category_id',
            'categories.id',
            $defaultAddress
        );
    }
}
