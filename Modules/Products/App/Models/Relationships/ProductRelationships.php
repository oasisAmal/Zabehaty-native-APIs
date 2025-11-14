<?php

namespace Modules\Products\App\Models\Relationships;

use Modules\Categories\App\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ProductRelationships
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}