<?php

namespace Modules\Shops\App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ShopScopes
{
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
}