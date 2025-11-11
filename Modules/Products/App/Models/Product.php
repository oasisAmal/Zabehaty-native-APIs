<?php

namespace Modules\Products\App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use CountryDatabaseTrait, TraitLanguage, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    /**
     * Translatable attributes for TraitLanguage
     *
     * @var array<string>
     */
    protected $translatable = [
        'name',
        'description',
        'brief',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'limited_offer_expired_at' => 'datetime',
    ];
}
