<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';

    protected $casts = [
        'is_default' => 'boolean',
        'allow_fast_delivery' => 'boolean',
        'delivery' => 'integer',
        'sort' => 'integer',
        'minimum_cart' => 'decimal:2',
    ];

    public function emirate()
    {
        return $this->belongsTo(Emirate::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function maslakh()
    {
        return $this->belongsTo(Maslakh::class, 'maslakh_id');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeActive($query)
    {
        return $query->where('delivery', '>', 0);
    }
}
