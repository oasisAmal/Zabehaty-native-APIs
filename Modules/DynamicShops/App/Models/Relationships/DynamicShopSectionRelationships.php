<?php

namespace Modules\DynamicShops\App\Models\Relationships;

use App\Models\Emirate;
use Modules\DynamicShops\App\Models\DynamicShopSectionItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait DynamicShopSectionRelationships
{
    /**
     * Get the emirate that owns the dynamic shop section.
     */
    public function emirate(): BelongsTo
    {
        return $this->belongsTo(Emirate::class);
    }

    /**
     * Items belonging to the dynamic shop section.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DynamicShopSectionItem::class, 'dynamic_shop_section_id');
    }
}
