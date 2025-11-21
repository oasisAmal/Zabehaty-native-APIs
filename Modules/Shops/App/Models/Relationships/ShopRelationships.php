<?php

namespace Modules\Shops\App\Models\Relationships;

use Modules\Categories\App\Models\Category;
use Modules\HomePage\App\Models\HomePageItem;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ShopRelationships
{
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'shop_categories', 'shop_id', 'category_id');
    }

    public function homePageItems(): MorphMany
    {
        return $this->morphMany(HomePageItem::class, 'item', 'item_type', 'item_id');
    }
}
