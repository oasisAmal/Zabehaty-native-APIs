<?php

namespace Modules\DynamicCategories\App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait DynamicCategorySectionScopes
{
    /**
     * Scope to order sections by their sorting value.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sorting');
    }
}

