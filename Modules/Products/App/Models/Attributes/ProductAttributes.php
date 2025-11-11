<?php

namespace Modules\Products\App\Models\Attributes;

trait ProductAttributes
{
    public function getImageUrlAttribute()
    {
        return $this->thumb ?? null;
    }
}