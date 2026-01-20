<?php

namespace Modules\Shops\App\Models;

use App\Models\Emirate;
use Modules\Shops\App\Models\Shop;
use Illuminate\Database\Eloquent\Model;

class ShopVisibility extends Model
{
    protected $table = 'shop_visibilities';

    public $timestamps = false;

    protected $fillable = [
        'shop_id',
        'emirate_id',
        'region_ids',
    ];

    protected $casts = [
        'emirate_id' => 'integer',
        'region_ids' => 'array',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function emirate()
    {
        return $this->belongsTo(Emirate::class);
    }
}
