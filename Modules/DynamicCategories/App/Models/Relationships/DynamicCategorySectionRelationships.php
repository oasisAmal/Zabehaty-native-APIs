<?php

namespace Modules\DynamicCategories\App\Models\Relationships;

use App\Models\Emirate;
use Modules\DynamicCategories\App\Models\DynamicCategorySectionItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait DynamicCategorySectionRelationships
{
    /**
     * Get the emirate that owns the dynamic category section.
     */
    public function emirate(): BelongsTo
    {
        return $this->belongsTo(Emirate::class);
    }

    /**
     * Items belonging to the dynamic category section.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DynamicCategorySectionItem::class, 'dynamic_category_section_id');
    }
}

