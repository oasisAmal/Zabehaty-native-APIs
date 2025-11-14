<?php

namespace Modules\Categories\App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait CategoryScopes
{
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
}