<?php

namespace Modules\Products\App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAddonSectionItem extends Model
{
    protected $table = 'product_addon_section_items';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'media' => 'array',
    ];
}
