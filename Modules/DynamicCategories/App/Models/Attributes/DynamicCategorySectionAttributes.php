<?php

namespace Modules\DynamicCategories\App\Models\Attributes;

trait DynamicCategorySectionAttributes
{
    /**
     * Resolve the title image URL based on the requested language.
     */
    public function getTitleImageUrlAttribute(): ?string
    {
        return $this->getAttribute('title_image_' . request()->app_lang . '_url') ?? null;
    }
}

