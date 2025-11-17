<?php

namespace Modules\Products\App\Models;

use Illuminate\Database\Eloquent\Model;

class SubProduct extends Model
{
    protected $table = 'sub_products';

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean'
    ];
}
