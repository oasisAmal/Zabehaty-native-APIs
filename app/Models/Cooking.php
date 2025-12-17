<?php

namespace App\Models;

use App\Traits\CountryDatabaseTrait;
use App\Traits\TraitLanguage;
use Illuminate\Database\Eloquent\Model;

class Cooking extends Model
{
    use CountryDatabaseTrait, TraitLanguage;

    protected $table = 'cookings';

    protected $translatable = [
        'name',
        'description',
    ];
}
