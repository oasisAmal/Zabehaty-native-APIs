<?php

namespace Modules\Categories\App\Models;

use App\Models\Emirate;
use Modules\Categories\App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class CategoryVisibility extends Model
{
    protected $table = 'category_visibilities';

    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'emirate_id',
        'region_ids',
    ];

    protected $casts = [
        'emirate_id' => 'integer',
        'region_ids' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function emirate()
    {
        return $this->belongsTo(Emirate::class);
    }
}
