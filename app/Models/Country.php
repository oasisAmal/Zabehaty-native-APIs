<?php

namespace App\Models;

use App\Traits\CountryDatabaseTrait;
use App\Traits\TraitLanguage;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use TraitLanguage, CountryDatabaseTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatable = ['name'];
}
