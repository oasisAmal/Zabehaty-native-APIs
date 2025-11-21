<?php

namespace Modules\HomePage\App\Models;

use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HomePageItem extends Model
{
    use CountryDatabaseTrait;

   /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'home_page_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'home_page_id',
        'image_ar_url',
        'image_en_url',
        'item_id',
        'item_type',
        'external_link'
    ];

    /**
     * Get the home page section that owns the item.
     */
    public function homePage(): BelongsTo
    {
        return $this->belongsTo(HomePage::class);
    }

    /**
     * Get the parent item model (polymorphic).
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
