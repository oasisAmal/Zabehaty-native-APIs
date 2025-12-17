<?php

namespace App\Models;

use App\Traits\CountryDatabaseTrait;
use App\Traits\TraitLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AddonSection extends Model
{
    use TraitLanguage, CountryDatabaseTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'addon_sections';

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
        static::addGlobalScope('addon_section_active', function (Builder $builder) {
            $builder->active();
        });
        static::addGlobalScope('addon_section_ordered', function (Builder $builder) {
            $builder->ordered();
        });
    }

    /**
     * Scope a query to only include active sections.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order the sections by position.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort');
    }

    /**
     * Get the items for the addon section.
     */
    public function items(): HasMany
    {
        return $this->hasMany(AddonSectionItem::class);
    }
}
