<?php

namespace Modules\Shops\App\Models\Attributes;

trait ShopAttributes
{
    public function getLogoUrlAttribute()
    {
        return $this->logo_url ?? null;
    }
}