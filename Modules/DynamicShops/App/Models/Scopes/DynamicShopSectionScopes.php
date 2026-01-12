<?php

namespace Modules\DynamicShops\App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait DynamicShopSectionScopes
{
    /**
     * Scope to order sections by their sorting value.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sorting');
    }
}
