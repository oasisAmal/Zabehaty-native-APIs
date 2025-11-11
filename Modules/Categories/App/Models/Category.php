<?php

namespace Modules\Categories\App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use CountryDatabaseTrait, TraitLanguage;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

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
    ];
}
