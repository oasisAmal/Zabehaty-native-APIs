<?php

namespace Modules\Products\App\Models;

use App\Models\Cooking;
use Illuminate\Database\Eloquent\Model;

class ProductCooking extends Model
{
    protected $table = 'product_cookings';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cooking()
    {
        return $this->belongsTo(Cooking::class);
    }
}
