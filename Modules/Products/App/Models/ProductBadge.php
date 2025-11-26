<?php

namespace Modules\Products\App\Models;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Model;

class ProductBadge extends Model
{
    protected $table = 'product_badges';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
