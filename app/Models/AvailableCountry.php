<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Traits\TraitLanguage;
use Illuminate\Database\Eloquent\Model;

class AvailableCountry extends Model
{
    use TraitLanguage;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'available_countries';

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatable = ['name'];

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

    /**
     * Get the flag URL attribute.
     *
     * @return string
     */
    public function getFlagUrlAttribute()
    {
        $flag = Str::replace('../', '', $this->flag);
        return asset('images/' . $flag);
    }
}
