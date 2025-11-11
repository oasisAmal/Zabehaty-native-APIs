<?php

namespace Modules\Categories\App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Modules\Categories\App\Models\Scopes\CategoryScopes;
use Modules\Categories\App\Models\Attributes\CategoryAttributes;
use Modules\Categories\App\Models\Relationships\CategoryRelationships;

class Category extends Model
{
    use CategoryAttributes, CategoryRelationships, CategoryScopes;
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
