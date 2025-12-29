<?php

namespace App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Region extends Model
{
    use HasSpatial, CountryDatabaseTrait, TraitLanguage;
    
    protected $table = 'regions';
    public $timestamps = false;

    protected $casts = [
        'polygon' => Polygon::class,
    ];

    protected $translatable = ['name'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function emirate()
    {
        return $this->belongsTo(Emirate::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function masalekh()
    {
        return $this->hasMany(Maslakh::class);
    }

    public function getpolygon()
    {
        if (!$this->polygon) return;

        $polygon =  $this->polygon->jsonSerialize()['coordinates'][0];

        $polygon = array_map(function ($item) {
            return [
                'lat' => $item[1],
                'lng' => $item[0],
            ];
        }, $polygon);
        return $polygon;
    }

    public static function pointInsideAny(float $lat, float $lng): self|null
    {
        $point = new Point($lat, $lng);
        return self::whereContains('polygon', $point)->first();
    }
}
