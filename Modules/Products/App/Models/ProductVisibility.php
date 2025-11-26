<?php

namespace Modules\Products\App\Models;

use App\Models\Emirate;
use Illuminate\Database\Eloquent\Model;

class ProductVisibility extends Model
{
    protected $table = 'product_visibilities';

    protected $casts = [
        'emirate_id' => 'integer',
        'region_ids' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function emirate()
    {
        return $this->belongsTo(Emirate::class);
    }
}
