<?php

namespace Modules\Shops\App\Models\Relationships;

use Modules\Categories\App\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait ShopRelationships
{
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'shop_categories', 'shop_id', 'category_id');
    }
}
