<?php

namespace Modules\HomePage\App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Modules\HomePage\App\Models\Scopes\HomePageScopes;
use Modules\HomePage\App\Models\Attributes\HomePageAttributes;
use Modules\HomePage\App\Models\Scopes\MatchedDefaultAddressScope;
use Modules\HomePage\App\Models\Relationships\HomePageRelationships;

class HomePage extends Model
{
    use HomePageAttributes, HomePageRelationships, HomePageScopes;
    use CountryDatabaseTrait, TraitLanguage;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'home_page';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'emirate_id',
        'region_ids',
        'title_en',
        'title_ar',
        'image_ar_url',
        'image_en_url',
        'background_image_url',
        'title_image_ar_url',
        'title_image_en_url',
        'type',
        'banner_size',
        'sorting',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
