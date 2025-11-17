<?php

namespace Modules\Shops\App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Illuminate\Database\Eloquent\Builder;

class ShopBranch extends Model
{
    protected $table = 'shop_branches';

    protected $casts = [
        'thirty_min_zone' => Polygon::class,
        'thirty_min_time' => 'array',
        'region_ids' => 'array',
    ];

    public function getThirtyMinZone()
    {
        if (!$this->thirty_min_zone) return null;

        // $polygon =  $this->thirty_min_zone->jsonSerialize()->getCoordinates()[0];
        $polygon = $this->thirty_min_zone->jsonSerialize()['coordinates'][0] ?? null;
        $polygon = array_map(function ($item) {
            return [
                'lat' => $item[1],
                'lng' => $item[0],
            ];
        }, $polygon);
        return $polygon ?? null;
    }


    public function scopeThirtyMinZone(Builder $query)
    {
        $query->whereNotNull('thirty_min_zone')
            ->where(function ($q) {
                $q->where('thirty_min_time->time_from_' . date('w'), '<=', date('H:i'));
                $q->where('thirty_min_time->time_to_' . date('w'), '>=', date('H:i'));
                $q->orWhereNull('thirty_min_time');
            })
        ;
    }
}
