<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Models\HomeSection;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class FeaturedSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build featured section data
     *
     * @param HomeSection $section
     * @return array
     */
    public function build(HomeSection $section): array
    {
        // This could be a combination of banners and products
        // or specific featured content based on settings
        
        $banners = $section->getActiveBanners();
        $products = $section->getActiveProducts();

        return [
            'banners' => $banners->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'image_url' => $banner->full_image_url,
                    'link' => $banner->link,
                ];
            }),
            'products' => $products->map(function ($sectionProduct) {
                $product = $sectionProduct->product;
                
                if (!$product) {
                    return null;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->image_url ?? null,
                    'discount_percentage' => $product->discount_percentage ?? 0,
                    'is_featured' => $product->is_featured ?? false,
                ];
            })->filter(),
            'settings' => $section->settings ?? [],
        ];
    }
}
