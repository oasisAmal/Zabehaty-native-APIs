<?php

namespace Modules\DynamicCategories\App\Models;

use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DynamicCategorySectionItem extends Model
{
    use CountryDatabaseTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynamic_category_section_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dynamic_category_section_id',
        'image_ar_url',
        'image_en_url',
        'item_id',
        'item_type',
        'external_link',
        'sorting',
    ];

    /**
     * Get the dynamic category section that owns the item.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(DynamicCategorySection::class);
    }

    /**
     * Get the parent item model (polymorphic).
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
