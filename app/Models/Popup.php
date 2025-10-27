<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PopupDataTypes;

class Popup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'target_page',
        'size',
        'image_ar_url',
        'image_en_url',
        'thumbnail_url',
        'video_url',
        'link',
        'item_type',
        'item_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active popups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getItemDataAttribute()
    {
        if (!$this->item_type || !$this->item_id) {
            return null;
        }

        return match ($this->item_type) {
            // PopupDataTypes::PRODUCT => ProductResource::make(Product::find($this->item_id)),
            // PopupDataTypes::SHOP => ShopResource::make(Shop::find($this->item_id)),
            // PopupDataTypes::CATEGORY => CategoryResource::make(Category::find($this->item_id)),
            default => null,
        };
    }
}
