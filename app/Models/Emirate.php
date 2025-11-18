<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emirate extends Model
{
    protected $table = 'emirates';

    protected $casts = [
        'is_active' => 'boolean',
        'delivery_time_nextday' => 'boolean',
        'delivery' => 'integer',
        'delivery_delay' => 'integer',
        'sort' => 'integer',
        'hours_before_order' => 'integer',
        'parent_id' => 'integer',
        'supplier_id' => 'integer',
        'delivery_times' => 'array',
        'delivery_times_ramadan' => 'array',
        'delivery_times_3eed' => 'array',
        'animals' => 'array',
        'prices' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(Emirate::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Emirate::class, 'parent_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function supplierLinks()
    {
        return $this->hasMany(SupplierLink::class);
    }

    public function masalekh()
    {
        return $this->hasMany(Maslakh::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithDelivery($query)
    {
        return $query->where('delivery', '>', 0);
    }
}
