<?php

namespace Modules\Shops\App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shops\App\Models\Attributes\ShopAttributes;
use Modules\Shops\App\Models\Relationships\ShopRelationships;
use Modules\Shops\App\Models\Scopes\ShopScopes;

class Shop extends Model
{
    use ShopAttributes, ShopRelationships, ShopScopes;
    use CountryDatabaseTrait, TraitLanguage, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shops';

    /**
     * Translatable attributes for TraitLanguage
     *
     * @var array<string>
     */
    protected $translatable = [
        'name',
        'description',
        'address',
    ];
}
