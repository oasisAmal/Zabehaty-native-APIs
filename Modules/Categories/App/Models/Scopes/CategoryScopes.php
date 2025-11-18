<?php

namespace Modules\Categories\App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait CategoryScopes
{
   public function scopeOnlyParents(Builder $query)
   {
      return $query->where('parent_id', null);
   }
}
