<?php

namespace Modules\Products\App\Models\Relationships;

use Modules\Shops\App\Models\Shop;
use Modules\Categories\App\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ProductRelationships
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}