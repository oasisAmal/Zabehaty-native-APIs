<?php

namespace Modules\Products\App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ProductScopes
{
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
}