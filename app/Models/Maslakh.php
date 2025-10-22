<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maslakh extends Model
{
    protected $table = 'masalekh';

    protected $fillable = [
        'emirate_id',
        'region_id',
        'name',
        'mobile',
        'land_line',
        'address_type',
        'address',
        'lat',
        'lng',
    ];

    protected $casts = [
        'lat' => 'double',
        'lng' => 'double',
    ];

    public function emirate()
    {
        return $this->belongsTo(Emirate::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function scopeNearby($query, $lat, $lng, $radius = 10)
    {
        return $query->selectRaw("*, 
            (6371 * acos(cos(radians(?)) 
            * cos(radians(lat)) 
            * cos(radians(lng) - radians(?)) 
            + sin(radians(?)) 
            * sin(radians(lat)))) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<', $radius)
            ->orderBy('distance');
    }
}
