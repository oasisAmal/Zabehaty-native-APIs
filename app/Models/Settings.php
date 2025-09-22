<?php

namespace App\Models;

use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use CountryDatabaseTrait;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The timestamps are disabled.
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'value' => 'array',
    ];
}
