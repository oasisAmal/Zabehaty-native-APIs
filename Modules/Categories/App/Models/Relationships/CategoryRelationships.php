<?php

namespace Modules\Categories\App\Models\Relationships;

use Modules\HomePage\App\Models\HomePageItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Categories\App\Models\CategoryVisibility;

trait CategoryRelationships
{
    public function homePageItems(): MorphMany
    {
        return $this->morphMany(HomePageItem::class, 'item', 'item_type', 'item_id');
    }

    public function categoryVisibilities(): HasMany
    {
        return $this->hasMany(CategoryVisibility::class);
    }
}
