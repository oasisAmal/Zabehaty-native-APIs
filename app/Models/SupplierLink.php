<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierLink extends Model
{
    protected $table = 'supplier_links';

    protected $fillable = [
        'supplier_id',
        'product_id',
        'region_id',
        'time_id',
        'emirate_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function emirate()
    {
        return $this->belongsTo(Emirate::class);
    }
}
