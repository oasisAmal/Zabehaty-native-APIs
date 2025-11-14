<?php

namespace Modules\HomePage\App\Models\Attributes;

trait HomePageAttributes
{
    /**
     * Get the title image URL attribute.
     *
     * @return string|null
     */
    public function getTitleImageUrlAttribute(): string|null
    {
        return $this->getAttribute('title_image_' . request()->app_lang . '_url') ?? null;
    }
}
