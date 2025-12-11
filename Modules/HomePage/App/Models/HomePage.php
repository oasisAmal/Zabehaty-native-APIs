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
