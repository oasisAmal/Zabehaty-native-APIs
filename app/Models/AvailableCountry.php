<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailableCountry extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'available_countries';


    /**
     * Scope a query to only include active countries
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}