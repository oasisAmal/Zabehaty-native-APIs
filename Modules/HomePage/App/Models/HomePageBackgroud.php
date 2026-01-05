<?php

namespace Modules\HomePage\App\Models;

use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Modules\HomePage\App\Models\Scopes\MatchedDefaultAddressScope;

class HomePageBackgroud extends Model
{
    use CountryDatabaseTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'home_page_backgrouds';

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
        static::addGlobalScope(new MatchedDefaultAddressScope());
    }
}
