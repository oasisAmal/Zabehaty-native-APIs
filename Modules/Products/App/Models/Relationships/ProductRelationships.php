<?php

namespace Modules\Products\App\Models\Relationships;

use App\Models\Badge;
use Modules\Shops\App\Models\Shop;
use Modules\Categories\App\Models\Category;
use Modules\Products\App\Models\SubProduct;
use Modules\HomePage\App\Models\HomePageItem;
use Modules\Products\App\Models\ProductBranch;
use Modules\Products\App\Models\ProductVisibility;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait ProductRelationships
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function department()
    {
        return $this->belongsTo(Category::class, 'department_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function productBranches()
    {
        return $this->hasMany(ProductBranch::class, 'product_id');
    }

    public function subProducts()
    {
        return $this->hasMany(SubProduct::class, 'product_id')
            ->orderByRaw("CAST(SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(data, '$.weight')), '-', 1) AS UNSIGNED)")
            ->orderBy('price');
    }

    public function homePageItems(): MorphMany
    {
        return $this->morphMany(HomePageItem::class, 'item', 'item_type', 'item_id');
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'product_badges', 'product_id', 'badge_id');
    }

    public function productVisibilities(): HasMany
    {
        return $this->hasMany(ProductVisibility::class);
    }
}
