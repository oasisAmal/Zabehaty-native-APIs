<?php

namespace Modules\HomePage\App\Models;

use App\Models\Emirate;
use App\Traits\TraitLanguage;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomePage extends Model
{
    use CountryDatabaseTrait, TraitLanguage;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'home_page';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'emirate_id',
        'region_ids',
        'title_en',
        'title_ar',
        'image_ar_url',
        'image_en_url',
        'background_image_url',
        'title_image_ar_url',
        'title_image_en_url',
        'type',
        'banner_size',
        'sorting',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'region_ids' => 'array',
        'sorting' => 'integer',
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
     * Get the emirate that owns the home page section.
     */
    public function emirate(): BelongsTo
    {
        return $this->belongsTo(Emirate::class);
    }

    /**
     * Get the items that belong to the home page section.
     */
    public function items(): HasMany
    {
        return $this->hasMany(HomePageItem::class, 'home_page_id');
    }

    /**
     * Scope to order by sorting.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sorting');
    }

    /**
     * Get the title image URL attribute.
     *
     * @return string|null
     */
    public function getTitleImageUrlAttribute(): string|null
    {
        return $this->getAttribute('title_image_' . request()->app_lang . '_url') ?? null;
    }
}
