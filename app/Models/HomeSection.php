<?php

namespace App\Models;

use Modules\HomePage\Enums\HomeSectionType;
use App\Traits\CountryDatabaseTrait;
use App\Traits\TraitLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeSection extends Model
{
    use CountryDatabaseTrait, TraitLanguage;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'title_en',
        'title_ar',
        'is_active',
        'position',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
        'settings' => 'array',
        'type' => HomeSectionType::class,
    ];

    /**
     * Translatable attributes for TraitLanguage
     *
     * @var array<string>
     */
    protected $translatable = [
        'title',
    ];

    /**
     * Get the banners for the section.
     */
    public function banners(): HasMany
    {
        return $this->hasMany(HomeBanner::class, 'section_id');
    }

    /**
     * Get the products for the section.
     */
    public function products(): HasMany
    {
        return $this->hasMany(SectionProduct::class, 'section_id');
    }

    /**
     * Scope to get only active sections
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by position
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Scope to filter by section type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param HomeSectionType $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, HomeSectionType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the section's active banners
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveBanners()
    {
        return $this->banners()->active()->ordered()->get();
    }

    /**
     * Get the section's active products
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveProducts()
    {
        return $this->products()->active()->ordered()->get();
    }
}
