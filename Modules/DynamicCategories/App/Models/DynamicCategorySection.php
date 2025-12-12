<?php

namespace Modules\DynamicCategories\App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Modules\DynamicCategories\App\Models\Scopes\MatchedDefaultAddressScope;
use Modules\DynamicCategories\App\Models\Scopes\DynamicCategorySectionScopes;
use Modules\DynamicCategories\App\Models\Attributes\DynamicCategorySectionAttributes;
use Modules\DynamicCategories\App\Models\Relationships\DynamicCategorySectionRelationships;

class DynamicCategorySection extends Model
{
    use DynamicCategorySectionAttributes, DynamicCategorySectionRelationships, DynamicCategorySectionScopes;
    use CountryDatabaseTrait, TraitLanguage;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynamic_category_sections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'emirate_ids',
        'region_ids',
        'title_en',
        'title_ar',
        'title_image_ar_url',
        'title_image_en_url',
        'background_image_url',
        'type',
        'display_type',
        'menu_type',
        'item_type',
        'banner_size',
        'sorting',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'emirate_ids' => 'array',
        'region_ids' => 'array',
        'sorting' => 'integer',
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
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        // static::addGlobalScope(new MatchedDefaultAddressScope());
    }
}
