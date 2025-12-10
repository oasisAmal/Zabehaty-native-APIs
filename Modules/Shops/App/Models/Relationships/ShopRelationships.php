<?php

namespace Modules\Shops\App\Models\Relationships;

use Modules\Categories\App\Models\Category;
use Modules\Shops\App\Models\ShopVisibility;
use Modules\HomePage\App\Models\HomePageItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\DynamicCategories\App\Models\DynamicCategorySectionItem;

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

    public function dynamicCategorySectionItems(): MorphMany
    {
        return $this->morphMany(DynamicCategorySectionItem::class, 'item', 'item_type', 'item_id');
    }

    public function shopVisibilities(): HasMany
    {
        return $this->hasMany(ShopVisibility::class);
    }
}
