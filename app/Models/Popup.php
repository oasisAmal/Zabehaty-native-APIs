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

    public function getMediaUrlAttribute()
    {
        if ($this->video_url) {
            return $this->video_url;
        }
        return request()->app_lang == 'ar' ? $this->image_ar_url : $this->image_en_url;
    }

    public function getMediaTypeAttribute()
    {
        if ($this->video_url) {
            return 'video';
        }
        return 'image';
    }
}
