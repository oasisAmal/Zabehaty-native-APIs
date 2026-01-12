<?php

namespace Modules\DynamicShops\App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DynamicShopSectionItem extends Model
{
    use CountryDatabaseTrait, TraitLanguage;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynamic_shop_section_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dynamic_shop_section_id',
        'menu_item_parent_id',
        'title_en',
        'title_ar',
        'image_ar_url',
        'image_en_url',
        'item_id',
        'item_type',
        'external_link',
        'is_all_menu_item',
        'sorting',
    ];

    /**
     * Translatable attributes for TraitLanguage
     *
     * @var array<string>
     */
    protected $translatable = [
        'title',
    ];

    /**
     * Get the dynamic shop section that owns the item.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(DynamicShopSection::class);
    }

    /**
     * Get the parent item model (polymorphic).
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
