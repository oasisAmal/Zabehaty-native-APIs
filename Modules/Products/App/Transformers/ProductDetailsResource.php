<?php

namespace Modules\Products\App\Transformers;

use Illuminate\Http\Request;
use App\Enums\CountryCurrencies;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $product = is_array($this->resource) ? $this->resource : (array) $this->resource;
        $sizes = $product['sub_products'] ?? [];
        $categoryId = $product['category_id'] ?? null;
        $addonSections = collect($product['addon_sections'] ?? []);

        return [
            'id' => $product['id'] ?? null,
            'name' => $product['name'] ?? null,
            'description_title' => $product['description_title'] ?? null,
            'description' => $product['description'] ?? null,
            'image_url' => $product['image'] ?? null,
            'images' => $product['images'] ?? [],
            'shop' => $product['shop_name'] ?? null,
            'category' => $product['category_name'] ?? null,
            'currency' => $product['currency'] ?? CountryCurrencies::getCurrency(),
            'price' => $product['price'] ?? null,
            'price_before_discount' => $product['price_before_discount'] ?? null,
            'discount_percentage' => $product['discount_percentage'] ?? null,
            'limited_offer_expired_at' => $product['limited_offer_expired_at'] ?? null,
            'badge' => $product['badge'] ?? null,
            'is_favorite' => (bool) ($product['is_favorite'] ?? false),
            'has_quantity' => $product['has_quantity'] ?? null,
            'quantity_settings' => $product['quantity_settings'] ?? null,
            'stock' => $product['stock'] ?? null,
            'size_section_name' => $product['size_section_name'] ?? null,
            'sizes' => collect($sizes)->map(function ($size) use ($categoryId) {
                $sizeArray = (array) $size;
                if (! array_key_exists('category_id', $sizeArray)) {
                    $sizeArray['category_id'] = $categoryId;
                }
                return new ProductSizeResource((object) $sizeArray);
            }),
            'addon_sections' => $addonSections->map(fn ($section) => new AddonSectionResource((object) $section)),
            'has_addons' => $addonSections->isNotEmpty(),
        ];
    }
}
