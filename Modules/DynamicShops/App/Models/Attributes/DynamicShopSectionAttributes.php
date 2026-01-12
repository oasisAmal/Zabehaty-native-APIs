<?php

namespace Modules\DynamicShops\App\Models\Attributes;

trait DynamicShopSectionAttributes
{
    /**
     * Resolve the title image URL based on the requested language.
     */
    public function getTitleImageUrlAttribute(): ?string
    {
        return $this->getAttribute('title_image_' . request()->app_lang . '_url') ?? null;
    }
}
