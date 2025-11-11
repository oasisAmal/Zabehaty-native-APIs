<?php

namespace Modules\HomePage\App\Services\Builders\Sections;

use App\Models\HomeSection;
use Modules\HomePage\App\Services\Builders\Interfaces\SectionBuilderInterface;

class ProductSectionBuilder implements SectionBuilderInterface
{
    /**
     * Build product section data
     *
     * @param HomeSection $section
     * @return array
     */
    public function build(HomeSection $section): array
    {
        $products = $section->getActiveProducts();

        return  $products->map(function ($sectionProduct) {
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
        })->filter()->toArray();
    }
}
