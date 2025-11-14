<?php

namespace Modules\Categories\App\Models\Attributes;

trait CategoryAttributes
{
    public function getImageUrlAttribute()
    {
        return $this->image_url ?? null;
    }
}