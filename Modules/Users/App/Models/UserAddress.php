<?php

namespace Modules\Users\App\Models;

use App\Models\Branch;
use App\Models\Region;
use App\Models\Emirate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use SoftDeletes;

    protected $table = 'user_address';

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'mobile',
        'country_code',
        'street_name',
        'apartment_num',
        'lat',
        'lng',
        'emirate_id',
        'region_id',
        'branch_id',
        'building_number',
        'notes',
        'directions',
        'main_type',
        'address_type',
        'is_gift',
        'receiver_name',
        'show_sender_name',
        'is_default',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emirate()
    {
        return $this->belongsTo(Emirate::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeNotDefault($query)
    {
        return $query->where('is_default', false);
    }
}


