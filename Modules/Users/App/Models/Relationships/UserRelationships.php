<?php

namespace Modules\Users\App\Models\Relationships;

use Spatie\Activitylog\LogOptions;
use Modules\Users\App\Models\UserAddress;

trait UserRelationships
{
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }
}
