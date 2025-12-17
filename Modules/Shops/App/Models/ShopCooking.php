<?php

namespace Modules\Shops\App\Models;

use App\Models\Cooking;
use Illuminate\Database\Eloquent\Model;

class ShopCooking extends Model
{
    protected $table = 'shop_cookings';

    public function cooking()
    {
        return $this->belongsTo(Cooking::class);
    }
}
