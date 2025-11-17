<?php

namespace Modules\HomePage\App\Models\Relationships;

use App\Models\Emirate;
use Modules\HomePage\App\Models\HomePageItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HomePageRelationships
{
    /**
     * Get the emirate that owns the home page section.
     */
    public function emirate(): BelongsTo
    {
        return $this->belongsTo(Emirate::class);
    }

    /**
     * Get the items that belong to the home page section.
     */
    public function items(): HasMany
    {
        return $this->hasMany(HomePageItem::class, 'home_page_id');
    }
}
