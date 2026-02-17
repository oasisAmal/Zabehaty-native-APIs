<?php

namespace Modules\DynamicCategories\App\Services\Builders\Sections;

use App\Enums\Pagination;
use Modules\Shops\App\Models\Shop;
use Modules\Products\App\Models\Product;
use Modules\Categories\App\Models\Category;
use Modules\DynamicCategories\App\Services\Builders\Concerns\UsesDynamicCategoriesQueryBuilder;
use Modules\DynamicCategories\App\Services\Builders\Interfaces\SectionBuilderInterface;

class BannerSectionBuilder implements SectionBuilderInterface
{
    use UsesDynamicCategoriesQueryBuilder;
    /**
     * Build banner section data
     *
     * @param array $dynamicCategorySection
     * @return array
     */
    public function build(array $dynamicCategorySection): array
    {
        $query = $this->getConnection()
            ->table('dynamic_category_section_items')
            ->select([
                'id',
                'image_ar_url',
                'image_en_url',
                'item_type',
                'item_id',
                'external_link',
            ])
            ->where('dynamic_category_section_id', $dynamicCategorySection['id'])
            ->orderBy('id')
            ->limit(Pagination::PER_PAGE);

        $this->applyBannerVisibility($query);

        $items = $query->get();

        return $items
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'image_url' => $this->getImageUrl($item),
                    'item_type' => $this->getItemTypeName($item->item_type),
                    'item_id' => $item->item_id ?? 0,
                    'external_link' => $item->external_link ?? '',
                ];
            })
            ->filter()
            ->toArray();
    }

    public function hasMoreItems(array $dynamicCategorySection): bool
    {
        $query = $this->getConnection()
            ->table('dynamic_category_section_items')
            ->where('dynamic_category_section_id', $dynamicCategorySection['id']);

        $this->applyBannerVisibility($query);

        return $query->count() > Pagination::PER_PAGE;
    }

    private function getImageUrl(object $item): string
    {
        if (request()->app_lang == 'ar') {
            return $item->image_ar_url ?? '';
        }
        return $item->image_en_url ?? '';
    }

    private function getItemTypeName(?string $itemType): string
    {
        return match ($itemType) {
            Product::class => 'product',
            Category::class => 'category',
            Shop::class => 'shop',
            default => '',
        };
    }

    private function applyBannerVisibility($query): void
    {
        $defaultAddress = $this->getDefaultAddress();
        if (! $defaultAddress) {
            return;
        }

        $productType = Product::class;
        $shopType = Shop::class;
        $categoryType = Category::class;

        // old implementation
        // $query->where(function ($outerQuery) use ($defaultAddress, $productType, $shopType, $categoryType) {
        //     $outerQuery->whereNull('item_id')
        //         ->orWhereNotIn('item_type', [$productType, $shopType, $categoryType])
        //         ->orWhere(function ($typedQuery) use ($defaultAddress, $productType, $shopType, $categoryType) {
        //             $typedQuery->where(function ($productQuery) use ($defaultAddress, $productType) {
        //                 $productQuery->where('item_type', $productType)
        //                     ->where(function ($productVisibilityQuery) use ($defaultAddress) {
        //                         $this->applyVisibilityExists(
        //                             $productVisibilityQuery,
        //                             'product_visibilities',
        //                             'product_id',
        //                             'dynamic_category_section_items.item_id',
        //                             $defaultAddress
        //                         );
        //                     })
        //                     ->where(function ($categoryVisibilityQuery) use ($defaultAddress) {
        //                         $this->applyCategoryVisibilityThroughProducts(
        //                             $categoryVisibilityQuery,
        //                             'dynamic_category_section_items.item_id',
        //                             $defaultAddress
        //                         );
        //                     })
        //                     ->where(function ($shopQuery) use ($defaultAddress) {
        //                         $shopQuery->whereExists(function ($subQuery) {
        //                             $subQuery->selectRaw('1')
        //                                 ->from('products')
        //                                 ->whereColumn('products.id', 'dynamic_category_section_items.item_id')
        //                                 ->whereNull('products.shop_id');
        //                         })
        //                         ->orWhere(function ($shopVisibilityQuery) use ($defaultAddress) {
        //                             $this->applyShopVisibilityThroughProducts(
        //                                 $shopVisibilityQuery,
        //                                 'dynamic_category_section_items.item_id',
        //                                 $defaultAddress
        //                             );
        //                         });
        //                     });
        //             })
        //             ->orWhere(function ($shopQuery) use ($defaultAddress, $shopType) {
        //                 $shopQuery->where('item_type', $shopType)
        //                     ->where(function ($shopVisibilityQuery) use ($defaultAddress) {
        //                         $this->applyShopVisibilityByShopId(
        //                             $shopVisibilityQuery,
        //                             'dynamic_category_section_items.item_id',
        //                             $defaultAddress
        //                         );
        //                     })
        //                     ->where(function ($categoryVisibilityQuery) use ($defaultAddress) {
        //                         $this->applyCategoryVisibilityThroughShopCategories(
        //                             $categoryVisibilityQuery,
        //                             'dynamic_category_section_items.item_id',
        //                             $defaultAddress
        //                         );
        //                     });
        //             })
        //             ->orWhere(function ($categoryQuery) use ($defaultAddress, $categoryType) {
        //                 $categoryQuery->where('item_type', $categoryType)
        //                     ->where(function ($categoryVisibilityQuery) use ($defaultAddress) {
        //                         $this->applyCategoryVisibilityByCategoryId(
        //                             $categoryVisibilityQuery,
        //                             'dynamic_category_section_items.item_id',
        //                             $defaultAddress
        //                         );
        //                     });
        //             });
        //         });
        // });

        // new implementation
        $query->where(function ($outerQuery) use ($defaultAddress, $productType, $shopType, $categoryType) {
            $outerQuery->whereNull('item_id')
                ->orWhereNotIn('item_type', [$productType, $shopType, $categoryType])
                ->orWhere(function ($typedQuery) use ($defaultAddress, $productType, $shopType, $categoryType) {
                    $typedQuery->where(function ($productQuery) use ($defaultAddress, $productType) {
                        $productQuery->where('item_type', $productType)
                            ->where(function ($productVisibilityQuery) use ($defaultAddress) {
                                $this->applyIsVisibleVisibilityExists(
                                    $productVisibilityQuery,
                                    'product_visibilities',
                                    'product_id',
                                    'dynamic_category_section_items.item_id',
                                    $defaultAddress
                                );
                            });
                    })
                        ->orWhere(function ($shopQuery) use ($defaultAddress, $shopType) {
                            $shopQuery->where('item_type', $shopType)
                                ->where(function ($shopVisibilityQuery) use ($defaultAddress) {
                                    $this->applyIsVisibleVisibilityExists(
                                        $shopVisibilityQuery,
                                        'shop_visibilities',
                                        'shop_id',
                                        'dynamic_category_section_items.item_id',
                                        $defaultAddress
                                    );
                                });
                        })
                        ->orWhere(function ($categoryQuery) use ($defaultAddress, $categoryType) {
                            $categoryQuery->where('item_type', $categoryType)
                                ->where(function ($categoryVisibilityQuery) use ($defaultAddress) {
                                    $this->applyIsVisibleVisibilityExists(
                                        $categoryVisibilityQuery,
                                        'category_visibilities',
                                        'category_id',
                                        'dynamic_category_section_items.item_id',
                                        $defaultAddress
                                    );
                                });
                        });
                });
        });
    }
}

