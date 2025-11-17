<?php

namespace Modules\HomePage\App\Models\Scopes;

trait HomePageScopes
{
    /**
     * Scope to order by sorting.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sorting');
    }
}
