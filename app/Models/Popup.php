<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'region_ids' => 'array',
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
        return $this->image_ar_url ? 'image' : ($this->image_en_url ? 'image' : 'none');
    }
}
