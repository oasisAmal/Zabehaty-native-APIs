<?php

namespace Modules\DynamicShops\App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Modules\DynamicShops\App\Models\Scopes\MatchedDefaultAddressScope;
use Modules\DynamicShops\App\Models\Scopes\DynamicShopSectionScopes;
use Modules\DynamicShops\App\Models\Attributes\DynamicShopSectionAttributes;
use Modules\DynamicShops\App\Models\Relationships\DynamicShopSectionRelationships;

class DynamicShopSection extends Model
{
    use DynamicShopSectionAttributes, DynamicShopSectionRelationships, DynamicShopSectionScopes;
    use CountryDatabaseTrait, TraitLanguage;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynamic_shop_sections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_id',
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
        static::addGlobalScope(new MatchedDefaultAddressScope());
    }
}
