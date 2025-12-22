<?php

namespace Modules\Products\App\Models;

use App\Models\AddonSectionItem;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model for product_addon_sections to allow eager loading pivot relations.
 */
class ProductAddonSection extends Pivot
{
    protected $table = 'product_addon_sections';

    public $incrementing = true;

    /**
     * Get the items for the addon section.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function itemsPivots(): BelongsToMany
    {
        return $this->belongsToMany(
            AddonSectionItem::class,
            'product_addon_section_items',
            'product_addon_section_id',
            'addon_section_item_id'
        )
            ->withPivot(['price', 'id as product_addon_section_item_id'])
            ->withTimestamps();
    }
}
