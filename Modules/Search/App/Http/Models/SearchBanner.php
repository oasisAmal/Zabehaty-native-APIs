<?php

namespace Modules\Search\App\Http\Models;

use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;

class SearchBanner extends Model
{
    use CountryDatabaseTrait;

    protected $table = 'search_banners';

    protected $fillable = [
        'emirate_ids',
        'region_ids',
        'image_ar_url',
        'image_en_url',
        'item_type',
        'item_id',
        'external_link',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'emirate_ids' => 'array',
        'region_ids' => 'array',
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
