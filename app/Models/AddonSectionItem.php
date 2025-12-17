<?php

namespace App\Models;

use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AddonSectionItem extends Model
{
    use TraitLanguage, CountryDatabaseTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'addon_section_items';

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatable = ['title'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope('addon_section_item_active', function (Builder $builder) {
            $builder->active();
        });
        static::addGlobalScope('addon_section_item_ordered', function (Builder $builder) {
            $builder->ordered();
        });
    }

    /**
     * Scope a query to only include active items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order the items by sort.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort');
    }
}
